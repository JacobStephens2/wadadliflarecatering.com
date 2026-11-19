<?php
namespace WadadliFlare;

/**
 * Unified contact and quote lead intake behind injected verification and mail ports.
 */
final class LeadIntake {
    public const QUOTE_EVENT_TYPES = [
        'Wedding',
        'Corporate Event',
        'Private Event',
        'Graduation',
        'Birthday',
        'Memorial Service',
        'BBQ',
        'Other',
    ];

    private const FORM_DEFINITIONS = [
        'contact' => [
            'table' => 'contact_submissions',
            'fields' => ['name', 'email', 'phone', 'message'],
            'required' => ['name', 'email', 'message'],
            'heading' => 'New Contact Form Submission',
            'subject_prefix' => 'New Contact Form Submission from ',
        ],
        'quote' => [
            'table' => 'quote_requests',
            'fields' => ['name', 'email', 'phone', 'event_type', 'event_date', 'guest_count', 'location', 'message'],
            'required' => ['name', 'email', 'event_type'],
            'heading' => 'New Quote Request',
            'subject_prefix' => 'New Quote Request from ',
        ],
    ];

    private const FIELD_MAX_LENGTHS = [
        'name' => 255,
        'email' => 255,
        'phone' => 50,
        'event_type' => 100,
        'location' => 255,
    ];

    private const MESSAGE_MAX_BYTES = 65535;
    private const RATE_LIMIT_MAX_PER_HOUR = 3;
    private const RATE_LIMIT_WINDOW_SECONDS = 3600;
    private const RATE_LIMIT_STALE_SECONDS = 7200;

    public function __construct(
        private readonly \PDO|\Closure $database,
        private readonly \Closure $verifyTurnstile,
        private readonly \Closure $sendMail,
        private readonly string $recipientEmail,
        private readonly string $rateLimitDir,
    ) {}

    /**
     * Handle a form page request before header output, returning escaped redisplay values.
     *
     * @return array{success: bool, error: string, values: array<string, string>}
     * @throws \InvalidArgumentException when $form is unsupported.
     */
    public function handlePageRequest(string $form, array $server, array $post): array {
        $this->assertSupportedForm($form);

        $fields = self::FORM_DEFINITIONS[$form]['fields'];
        $method = is_string($server['REQUEST_METHOD'] ?? null)
            ? strtoupper($server['REQUEST_METHOD'])
            : 'GET';

        if ($method !== 'POST') {
            return [
                'success' => false,
                'error' => '',
                'values' => array_fill_keys($fields, ''),
            ];
        }

        $clientIp = is_string($server['REMOTE_ADDR'] ?? null) && $server['REMOTE_ADDR'] !== ''
            ? $server['REMOTE_ADDR']
            : 'unknown';
        $result = $this->submit($form, $post, $clientIp);

        $values = [];
        foreach ($fields as $field) {
            $raw = $post[$field] ?? '';
            $values[$field] = self::escapeHtml(is_string($raw) ? $raw : '');
        }

        return [
            'success' => $result['success'],
            'error' => $result['error'],
            'values' => $values,
        ];
    }

    /**
     * Render the shared hidden honeypot and Cloudflare Turnstile widget markup.
     */
    public function renderSpamGuards(string $siteKey): string {
        $escapedSiteKey = self::escapeHtml($siteKey);
        return '<div style="position:absolute;left:-9999px;" aria-hidden="true">' . "\n"
            . '    <label for="website_url">Leave this empty</label>' . "\n"
            . '    <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off" value="">' . "\n"
            . '</div>' . "\n"
            . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' . "\n"
            . '<div class="cf-turnstile" data-sitekey="' . $escapedSiteKey . '" data-theme="light"></div>' . "\n";
    }

    /**
     * Process a single contact or quote submission through the anti-spam, dedupe, persistence, and mail pipeline.
     *
     * @return array{success: bool, error: string}
     * @throws \InvalidArgumentException when $form is unsupported, before any I/O.
     */
    public function submit(string $form, array $input, string $clientIp): array {
        $this->assertSupportedForm($form);
        $ip = $clientIp !== '' ? $clientIp : 'unknown';

        [$data, $malformed] = $this->extractFormData($form, $input);
        if (!empty($input['website_url'])) {
            $this->logRejection($form, 'honeypot', $ip);
            return ['success' => true, 'error' => ''];
        }

        $validationError = $this->validateData($form, $data, $malformed);
        $saved = $this->toDatabasePayload($form, $data);
        $flagReason = null;
        $lock = null;
        $pdo = null;

        try {
            try {
                if ($validationError === '') {
                    $pdo = $this->openPdo();
                    // Serialize through verification and save. A retry may reuse an already
                    // consumed Turnstile token; only exact saved leads bypass the gates.
                    $lock = $this->acquireLock($pdo, $form, $data['email']);
                    if ($this->isDuplicateSubmission($pdo, $form, $saved)) {
                        $this->logRejection($form, 'duplicate', $ip);
                        return ['success' => true, 'error' => ''];
                    }
                }
                if (!$this->checkRateLimit($form, $ip)) {
                    $this->logRejection($form, 'rate limit', $ip);
                    return ['success' => false, 'error' => 'Please wait before submitting again.'];
                }
                $token = $input['cf-turnstile-response'] ?? '';
                if (!is_string($token) || $token === '' || !($this->verifyTurnstile)($token, $ip)) {
                    $this->logRejection($form, 'turnstile', $ip);
                    return ['success' => false, 'error' => 'Verification failed. Please try again.'];
                }
                if ($validationError !== '') {
                    $this->logRejection($form, 'validation', $ip);
                    return ['success' => false, 'error' => $validationError];
                }
                $flagReason = $this->detectSpamReason($data);
                $pdo = $pdo ?? $this->openPdo();
                $this->saveSubmission($pdo, $form, $saved, $flagReason);
            } finally {
                if ($lock !== null && $pdo !== null) {
                    $this->releaseLock($pdo, $lock);
                }
            }
        } catch (\Throwable) {
            // Database/lock/filesystem exceptions can contain submitted values; do not log their text.
            $this->logRejection($form, 'processing', $ip);
            return [
                'success' => false,
                'error' => 'Sorry, there was an error processing your request. Please try again or call us directly.',
            ];
        }

        return $this->deliverNotification($form, $data, $flagReason);
    }

    private function assertSupportedForm(string $form): void {
        if (!isset(self::FORM_DEFINITIONS[$form])) {
            throw new \InvalidArgumentException('Unsupported form: ' . $form);
        }
    }

    /**
     * @return array{0: array<string, string>, 1: bool}
     */
    private function extractFormData(string $form, array $input): array {
        $data = [];
        $malformed = false;
        foreach (self::FORM_DEFINITIONS[$form]['fields'] as $field) {
            $value = $input[$field] ?? '';
            $malformed = $malformed || !is_string($value);
            $data[$field] = is_string($value) ? trim($value) : '';
        }
        return [$data, $malformed];
    }

    private function validateData(string $form, array $data, bool $malformed): string {
        if ($malformed) {
            return 'Please enter valid text in each field.';
        }
        foreach (self::FORM_DEFINITIONS[$form]['required'] as $field) {
            if ($data[$field] === '') {
                return 'Please fill in all required fields.';
            }
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }
        foreach (self::FIELD_MAX_LENGTHS as $field => $limit) {
            if (isset($data[$field]) && mb_strlen($data[$field], 'UTF-8') > $limit) {
                return 'Please shorten the ' . str_replace('_', ' ', $field) . ' field.';
            }
        }
        // MySQL TEXT stores at most 65,535 bytes, including multibyte characters.
        if (strlen($data['message']) > self::MESSAGE_MAX_BYTES) {
            return 'Please shorten the message field.';
        }
        foreach ($data as $value) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                return 'Please enter valid text in each field.';
            }
        }
        if ($form === 'quote') {
            return $this->validateQuoteFields($data);
        }
        return '';
    }

    private function validateQuoteFields(array $data): string {
        if (!in_array($data['event_type'], self::QUOTE_EVENT_TYPES, true)) {
            return 'Please select a valid event type.';
        }
        if ($data['event_date'] !== '') {
            if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $data['event_date'])) {
                return 'Please enter a valid event date.';
            }
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $data['event_date']);
            if (!$date || $date->format('Y-m-d') !== $data['event_date'] || $data['event_date'] < '1000-01-01') {
                return 'Please enter a valid event date.';
            }
        }
        if (
            $data['guest_count'] !== ''
            && filter_var($data['guest_count'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false
        ) {
            return 'Please enter a valid number of guests.';
        }
        return '';
    }

    private function toDatabasePayload(string $form, array $data): array {
        $saved = $data;
        if ($form === 'quote') {
            $saved['event_date'] = $data['event_date'] === '' ? null : $data['event_date'];
            $saved['guest_count'] = $data['guest_count'] === '' ? null : (int)$data['guest_count'];
        }
        return $saved;
    }

    private function openPdo(): \PDO {
        if ($this->database instanceof \PDO) {
            return $this->database;
        }
        $pdo = ($this->database)();
        if (!$pdo instanceof \PDO) {
            throw new \RuntimeException('Database provider did not return a PDO instance');
        }
        return $pdo;
    }

    private function acquireLock(\PDO $pdo, string $form, string $email): string {
        $key = hash('sha256', self::FORM_DEFINITIONS[$form]['table'] . ':' . strtolower($email));
        $statement = $pdo->prepare('SELECT GET_LOCK(?, 10)');
        $statement->execute([$key]);
        if ((int)$statement->fetchColumn() !== 1) {
            throw new \RuntimeException('Submission lock unavailable');
        }
        return $key;
    }

    private function releaseLock(\PDO $pdo, string $lockKey): void {
        $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockKey]);
    }

    /**
     * Check exact persisted content, including rows saved before flag_reason existed.
     */
    private function isDuplicateSubmission(\PDO $pdo, string $form, array $saved): bool {
        $table = self::FORM_DEFINITIONS[$form]['table'];
        $conditions = [];
        foreach (array_keys($saved) as $field) {
            $conditions[] = $field === 'email' ? 'email = ?' : "BINARY $field <=> ?";
        }
        $where = implode(' AND ', $conditions);
        $statement = $pdo->prepare("SELECT id FROM $table WHERE $where AND submitted_at >= NOW() - INTERVAL 10 MINUTE LIMIT 1");
        $statement->execute(array_values($saved));
        return $statement->fetchColumn() !== false;
    }

    private function saveSubmission(\PDO $pdo, string $form, array $saved, ?string $flagReason): void {
        $table = self::FORM_DEFINITIONS[$form]['table'];
        $saved['flag_reason'] = $flagReason;
        $columns = implode(', ', array_keys($saved));
        $placeholders = implode(', ', array_fill(0, count($saved), '?'));
        $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)")->execute(array_values($saved));
    }

    private function checkRateLimit(string $form, string $clientIp): bool {
        if (!is_dir($this->rateLimitDir) && !mkdir($this->rateLimitDir, 0750, true) && !is_dir($this->rateLimitDir)) {
            throw new \RuntimeException('Could not create rate limit directory');
        }

        $file = $this->rateLimitDir . '/' . md5($clientIp . $form) . '.json';
        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            throw new \RuntimeException('Could not open rate limit file');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException('Could not lock rate limit file');
            }
            $contents = stream_get_contents($handle);
            $decoded = is_string($contents) && $contents !== '' ? json_decode($contents, true) : [];
            $timestamps = is_array($decoded) ? $decoded : [];

            $now = time();
            $windowStart = $now - self::RATE_LIMIT_WINDOW_SECONDS;
            $timestamps = array_values(array_filter(
                $timestamps,
                static fn($timestamp): bool => is_int($timestamp) && $timestamp > $windowStart
            ));

            if (count($timestamps) >= self::RATE_LIMIT_MAX_PER_HOUR) {
                return false;
            }

            $timestamps[] = $now;
            $encoded = json_encode($timestamps);
            if (
                $encoded === false
                || !rewind($handle)
                || !ftruncate($handle, 0)
                || fwrite($handle, $encoded) === false
                || !fflush($handle)
            ) {
                throw new \RuntimeException('Could not write rate limit file');
            }

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
            $this->purgeStaleRateLimits();
        }
    }

    private function purgeStaleRateLimits(): void {
        if (!is_dir($this->rateLimitDir)) {
            return;
        }
        $files = glob($this->rateLimitDir . '/*.json');
        if ($files === false) {
            return;
        }

        $staleBefore = time() - self::RATE_LIMIT_STALE_SECONDS;
        foreach ($files as $file) {
            clearstatcache(true, $file);
            $mtime = @filemtime($file);
            if ($mtime === false || $mtime >= $staleBefore) {
                continue;
            }

            $handle = @fopen($file, 'r+');
            if ($handle === false) {
                continue;
            }
            try {
                if (!flock($handle, LOCK_EX | LOCK_NB)) {
                    continue;
                }
                clearstatcache(true, $file);
                $lockedMtime = @filemtime($file);
                if ($lockedMtime !== false && $lockedMtime < $staleBefore) {
                    @unlink($file);
                }
            } finally {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
    }

    private function detectSpamReason(array $data): ?string {
        foreach ($data as $field => $value) {
            $reason = self::matchSpamPattern($value);
            if ($reason !== null) {
                return $field . ': ' . $reason;
            }
        }
        return null;
    }

    private static function matchSpamPattern(string $value): ?string {
        if (preg_match('/https?:\/\//i', $value) === 1) {
            return 'Contains a URL';
        }
        if (preg_match('/\b(?:seo services|buy backlinks|viagra|posture corrector)\b/i', $value) === 1) {
            return 'Unrelated advertising';
        }
        foreach ([
            'contactpageads\.com',
            'send your ad to.*million.*sites',
            '\$49.*million.*sites',
            'million sites.*\$49',
        ] as $pattern) {
            if (preg_match('/' . $pattern . '/i', $value) === 1) {
                return 'Mass advertising solicitation';
            }
        }
        return null;
    }

    /**
     * @return array{success: bool, error: string}
     */
    private function deliverNotification(string $form, array $data, ?string $flagReason): array {
        $message = $this->buildNotificationEmail($form, $data, $flagReason);
        try {
            $sent = (bool)($this->sendMail)($message);
        } catch (\Throwable) {
            $sent = false;
        }

        return [
            'success' => $sent,
            'error' => $sent
                ? ''
                : 'Your request was saved but we had trouble sending the email. We will still receive your request.',
        ];
    }

    /**
     * @return array{to: string, subject: string, body: string, reply_to: string, reply_to_name: string}
     */
    private function buildNotificationEmail(string $form, array $data, ?string $flagReason): array {
        $definition = self::FORM_DEFINITIONS[$form];
        $subject = self::notificationSubject($definition['subject_prefix'], $data['name'], $flagReason);
        $replyTo = $data['email'];
        $replyToName = preg_replace('/[\r\n]+/', ' ', $data['name']) ?? '';

        $fields = [
            ['label' => 'Name', 'value' => $data['name'], 'kind' => 'text'],
            ['label' => 'Email', 'value' => $data['email'], 'kind' => 'email'],
            ['label' => 'Phone', 'value' => $data['phone'], 'kind' => 'text'],
        ];
        if ($form === 'quote') {
            $fields[] = ['label' => 'Event Type', 'value' => $data['event_type'], 'kind' => 'text'];
            $fields[] = ['label' => 'Event Date', 'value' => self::formatEventDate($data['event_date']), 'kind' => 'text'];
            $fields[] = ['label' => 'Guest Count', 'value' => $data['guest_count'], 'kind' => 'text'];
            $fields[] = ['label' => 'Location', 'value' => $data['location'], 'kind' => 'text'];
        }
        $fields[] = ['label' => 'Message', 'value' => $data['message'], 'kind' => 'multiline'];

        $renderedFields = '';
        foreach ($fields as $field) {
            $label = self::escapeHtml($field['label']);
            $escapedValue = self::escapeHtml($field['value']);
            if ($field['kind'] === 'email') {
                $content = "<a href='mailto:$escapedValue'>$escapedValue</a>";
            } elseif ($field['kind'] === 'multiline') {
                $content = "<br>\n                    " . nl2br($escapedValue);
            } else {
                $content = $escapedValue;
            }
            $renderedFields .= "                <div class='field'>\n"
                . "                    <span class='label'>$label:</span> $content\n"
                . "                </div>\n";
        }

        $heading = self::escapeHtml($definition['heading']);
        $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d4af37; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #333; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>$heading</h2>
            </div>
            <div class='content'>
$renderedFields            </div>
        </div>
    </body>
    </html>
    ";

        return [
            'to' => $this->recipientEmail,
            'subject' => $subject,
            'body' => $body,
            'reply_to' => $replyTo,
            'reply_to_name' => $replyToName,
        ];
    }

    private static function notificationSubject(string $prefix, string $name, ?string $flagReason): string {
        $cleanName = str_replace(['<', '>'], '', strip_tags($name));
        $cleanName = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $cleanName) ?? '';
        return ($flagReason !== null ? '[Possible spam] ' : '') . $prefix . $cleanName;
    }

    private static function formatEventDate(string $rawDate): string {
        if ($rawDate === '') {
            return 'Not specified';
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $rawDate);
        return $date !== false ? $date->format('F j, Y') : $rawDate;
    }

    private function logRejection(string $form, string $reason, string $clientIp): void {
        error_log('Form rejection ' . json_encode([
            'form' => $form,
            'reason' => $reason,
            'ip' => $clientIp !== '' ? $clientIp : 'unknown',
        ], JSON_UNESCAPED_SLASHES));
    }

    private static function escapeHtml(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
