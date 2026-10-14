<?php
/**
 * Shared contact/quote submission pipeline: validate, flag, save and notify.
 */
require_once __DIR__ . '/email.php';
require_once __DIR__ . '/spam_protection.php';

const QUOTE_EVENT_TYPES = [
    'Wedding', 'Corporate Event', 'Private Event', 'Graduation',
    'Birthday', 'Memorial Service', 'BBQ', 'Other',
];

function form_validation_error(string $form, array $data, bool $malformed): string {
    if ($malformed) {
        return 'Please enter valid text in each field.';
    }
    $required = $form === 'quote' ? ['name', 'email', 'event_type'] : ['name', 'email', 'message'];
    foreach ($required as $field) {
        if ($data[$field] === '') {
            return 'Please fill in all required fields.';
        }
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    foreach (['name' => 255, 'email' => 255, 'phone' => 50, 'event_type' => 100, 'location' => 255] as $field => $limit) {
        if (isset($data[$field]) && mb_strlen($data[$field], 'UTF-8') > $limit) {
            return 'Please shorten the ' . str_replace('_', ' ', $field) . ' field.';
        }
    }
    // MySQL TEXT stores at most 65,535 bytes, including multibyte characters.
    if (strlen($data['message']) > 65535) {
        return 'Please shorten the message field.';
    }
    foreach ($data as $value) {
        if (!mb_check_encoding($value, 'UTF-8')) {
            return 'Please enter valid text in each field.';
        }
    }
    if ($form === 'quote') {
        if (!in_array($data['event_type'], QUOTE_EVENT_TYPES, true)) {
            return 'Please select a valid event type.';
        }
        if ($data['event_date'] !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['event_date']);
            if (!$date || $date->format('Y-m-d') !== $data['event_date'] || $data['event_date'] < '1000-01-01') {
                return 'Please enter a valid event date.';
            }
        }
        if ($data['guest_count'] !== '' && filter_var($data['guest_count'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false) {
            return 'Please enter a valid number of guests.';
        }
    }
    return '';
}

/**
 * Serialize submissions for one form/email so concurrent retries cannot both insert.
 * Compare persisted fields exactly (except email address case), including legacy rows.
 */
function save_form_submission(PDO $pdo, string $form, array $data, ?string $flagReason): bool {
    $table = $form === 'quote' ? 'quote_requests' : 'contact_submissions';
    $lock = hash('sha256', $table . ':' . strtolower($data['email']));
    $statement = $pdo->prepare('SELECT GET_LOCK(?, 10)');
    $statement->execute([$lock]);
    if ((int)$statement->fetchColumn() !== 1) {
        throw new RuntimeException('Submission lock unavailable');
    }
    try {
        $conditions = [];
        foreach (array_keys($data) as $field) {
            $conditions[] = $field === 'email' ? 'email = ?' : "BINARY $field <=> ?";
        }
        $where = implode(' AND ', $conditions);
        $statement = $pdo->prepare("SELECT id FROM $table WHERE $where AND submitted_at >= NOW() - INTERVAL 10 MINUTE LIMIT 1");
        $statement->execute(array_values($data));
        if ($statement->fetchColumn() !== false) {
            return false;
        }
        $data['flag_reason'] = $flagReason;
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)")->execute(array_values($data));
        return true;
    } finally {
        $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]);
    }
}

function process_form_submission(PDO $pdo, string $form, array $post): array {
    $fields = $form === 'quote'
        ? ['name', 'email', 'phone', 'event_type', 'event_date', 'guest_count', 'location', 'message']
        : ['name', 'email', 'phone', 'message'];
    $data = [];
    $malformed = false;
    foreach ($fields as $field) {
        $value = $post[$field] ?? '';
        $malformed = $malformed || !is_string($value);
        $data[$field] = is_string($value) ? trim($value) : '';
    }
    if (check_honeypot()) {
        log_form_rejection($form, 'honeypot');
        return ['success' => true, 'error' => ''];
    }
    if (!check_rate_limit($form, 3)) {
        log_form_rejection($form, 'rate limit');
        return ['success' => false, 'error' => 'Please wait before submitting again.'];
    }
    $token = $post['cf-turnstile-response'] ?? '';
    if (!is_string($token) || !verify_turnstile($token)) {
        log_form_rejection($form, 'turnstile');
        return ['success' => false, 'error' => 'Verification failed. Please try again.'];
    }
    $error = form_validation_error($form, $data, $malformed);
    if ($error !== '') {
        log_form_rejection($form, 'validation');
        return ['success' => false, 'error' => $error];
    }
    $flagReason = null;
    foreach ($data as $field => $value) {
        $reason = is_spam_content($value, '') ?? isSpamMessage($value);
        if ($reason !== null) {
            $flagReason = $field . ': ' . $reason;
            break;
        }
    }
    try {
        $saved = $data;
        if ($form === 'quote') {
            $saved['event_date'] = $data['event_date'] === '' ? null : $data['event_date'];
            $saved['guest_count'] = $data['guest_count'] === '' ? null : (int)$data['guest_count'];
        }
        if (!save_form_submission($pdo, $form, $saved, $flagReason)) {
            log_form_rejection($form, 'duplicate');
            return ['success' => true, 'error' => ''];
        }
        $sent = $form === 'quote'
            ? sendQuoteRequestEmail($data, $flagReason)
            : sendContactEmail($data['name'], $data['email'], $data['phone'], $data['message'], $flagReason);
        return ['success' => $sent, 'error' => $sent ? '' : 'Your request was saved but we had trouble sending the email. We will still receive your request.'];
    } catch (Exception $error) {
        // Database exceptions can contain submitted values; do not log their text.
        log_form_rejection($form, 'processing');
        return ['success' => false, 'error' => 'Sorry, there was an error processing your request. Please try again or call us directly.'];
    }
}
