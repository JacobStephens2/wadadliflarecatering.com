<?php
// Refuses a database connection when the user or password is missing or empty.
// PHP 8.3 with PDO MySQL. No server, Composer, credentials, or network.

function expect(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * @param array<string, mixed>|null $config Null reads the environment instead of a config array.
 * @param array<string, string> $env
 * @return array{opened: bool, message: string}
 */
function open_attempt(?array $config, array $env = []): array {
    $runner = <<<'PHP'
define('ENV_LOADED', true);
require $argv[1];
foreach (json_decode($argv[3], true) as $key => $value) {
    $_ENV[$key] = $value;
    putenv($key . '=' . $value);
}
try {
    $config = json_decode($argv[2], true);
    if ($config === null) {
        open_database();
    } else {
        open_database($config);
    }
    echo "opened\n";
} catch (PDOException $error) {
    echo "exception\n" . $error->getMessage() . "\n";
}
PHP;
    $process = proc_open(
        [PHP_BINARY, '-r', $runner, __DIR__ . '/../private/includes/db.php', json_encode($config), json_encode($env)],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        null,
        []
    );
    expect(is_resource($process), 'PHP subprocess must start');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    expect(proc_close($process) === 0 && $errors === '', 'open_database subprocess failed: ' . $errors . $output);
    $lines = explode("\n", rtrim($output, "\n"), 2);
    expect(($lines[0] ?? '') === 'opened' || ($lines[0] ?? '') === 'exception', 'Unexpected open_database result: ' . $output);
    return ['opened' => $lines[0] === 'opened', 'message' => $lines[1] ?? ''];
}

$absentSocket = sys_get_temp_dir() . '/wadadli-absent-' . bin2hex(random_bytes(6)) . '.sock';
$host = 'localhost;unix_socket=' . $absentSocket;
$user = 'db-user-marker-9f3a';

$refused = open_attempt([
    'host' => $host,
    'name' => 'wadadli_flare',
    'user' => $user,
    'pass' => '',
]);
expect($refused['opened'] === false, 'Empty password must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Empty password must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $user), 'Refusal must not contain the database user');
expect(!str_contains($refused['message'], $absentSocket), 'Refusal must not come from a socket connection attempt');
echo "PASS empty password is refused before connecting\n";

$password = 'db-pass-marker-9f3a';
$refused = open_attempt([
    'host' => $host,
    'name' => 'wadadli_flare',
    'user' => '',
    'pass' => $password,
]);
expect($refused['opened'] === false, 'Empty user must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Empty user must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $password), 'Refusal must not contain the database password');
expect(!str_contains($refused['message'], $absentSocket), 'Refusal must not come from a socket connection attempt');
echo "PASS empty user is refused before connecting\n";

$refused = open_attempt([
    'host' => $host,
    'name' => 'wadadli_flare',
    'pass' => $password,
]);
expect($refused['opened'] === false, 'Missing user must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Missing user must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $password), 'Refusal must not contain the database password');
echo "PASS missing user is refused before connecting\n";

$refused = open_attempt([
    'host' => $host,
    'name' => 'wadadli_flare',
    'user' => $user,
]);
expect($refused['opened'] === false, 'Missing password must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Missing password must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $user), 'Refusal must not contain the database user');
echo "PASS missing password is refused before connecting\n";

$refused = open_attempt(['host' => $host, 'name' => 'wadadli_flare']);
expect($refused['opened'] === false && !str_contains($refused['message'], 'SQLSTATE'), 'Missing user and password must be refused before connecting: ' . $refused['message']);
echo "PASS missing user and password are refused before connecting\n";

$refused = open_attempt(null, [
    'DB_HOST' => $host,
    'DB_NAME' => 'wadadli_flare',
    'DB_USER' => $user,
    'DB_PASS' => '',
]);
expect($refused['opened'] === false, 'Empty DB_PASS must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Empty DB_PASS must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $user), 'Refusal must not contain the database user');
echo "PASS empty DB_PASS is refused before connecting\n";

$refused = open_attempt(null, [
    'DB_HOST' => $host,
    'DB_NAME' => 'wadadli_flare',
    'DB_USER' => '',
    'DB_PASS' => $password,
]);
expect($refused['opened'] === false, 'Empty DB_USER must not open a connection');
expect(!str_contains($refused['message'], 'SQLSTATE'), 'Empty DB_USER must be refused before connecting: ' . $refused['message']);
expect(!str_contains($refused['message'], $password), 'Refusal must not contain the database password');
echo "PASS empty DB_USER is refused before connecting\n";

$refused = open_attempt(null, [
    'DB_HOST' => $host,
    'DB_NAME' => 'wadadli_flare',
    'DB_PASS' => $password,
]);
expect($refused['opened'] === false && !str_contains($refused['message'], $password) && !str_contains($refused['message'], 'SQLSTATE'), 'Missing DB_USER must be refused before connecting: ' . $refused['message']);
echo "PASS missing DB_USER is refused before connecting\n";

$refused = open_attempt(null, [
    'DB_HOST' => $host,
    'DB_NAME' => 'wadadli_flare',
    'DB_USER' => $user,
]);
expect($refused['opened'] === false && !str_contains($refused['message'], $user) && !str_contains($refused['message'], 'SQLSTATE'), 'Missing DB_PASS must be refused before connecting: ' . $refused['message']);
echo "PASS missing DB_PASS is refused before connecting\n";

$refused = open_attempt(null, []);
expect($refused['opened'] === false && !str_contains($refused['message'], 'SQLSTATE'), 'Unset database credentials must be refused before connecting: ' . $refused['message']);
echo "PASS unset database credentials are refused before connecting\n";

$refused = open_attempt(
    ['host' => $host, 'name' => 'wadadli_flare', 'user' => $user, 'pass' => ''],
    ['DB_USER' => $user, 'DB_PASS' => $password]
);
expect($refused['opened'] === false && !str_contains($refused['message'], 'SQLSTATE') && !str_contains($refused['message'], $password), 'A config array must not take a password from the environment: ' . $refused['message']);
echo "PASS config password is not replaced from the environment\n";

$attempted = open_attempt([
    'host' => $host,
    'name' => 'wadadli_flare',
    'user' => $user,
    'pass' => $password,
]);
expect($attempted['opened'] === false, 'Absent socket must not open');
expect(str_contains($attempted['message'], 'SQLSTATE'), 'Non-empty config credentials must still be offered to the server: ' . $attempted['message']);
expect(!str_contains($attempted['message'], $password), 'Connection failure must not contain the database password');
echo "PASS non-empty config credentials are not replaced\n";

$attempted = open_attempt(null, [
    'DB_HOST' => $host,
    'DB_NAME' => 'wadadli_flare',
    'DB_USER' => $user,
    'DB_PASS' => $password,
]);
expect(str_contains($attempted['message'], 'SQLSTATE'), 'Non-empty environment credentials must still be offered to the server: ' . $attempted['message']);
expect(!str_contains($attempted['message'], $password), 'Connection failure must not contain the database password');
expect(!str_contains($attempted['message'], $user), 'Socket failure must not contain the database user');
echo "PASS non-empty environment credentials are not replaced\n";

$attempted = open_attempt([
    'host' => $host,
    'user' => $user,
    'pass' => $password,
]);
expect(str_contains($attempted['message'], 'SQLSTATE'), 'Default database name must still be offered to the server: ' . $attempted['message']);
expect(!str_contains($attempted['message'], $password), 'Connection failure must not contain the database password');
echo "PASS omitted database name still attempts a connection\n";
