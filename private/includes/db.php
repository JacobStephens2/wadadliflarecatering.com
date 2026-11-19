<?php
/**
 * Database Connection
 * Connects to MySQL database using injected configuration or credentials from .env file
 */

/**
 * Open a lazy PDO MySQL connection, throwing PDOException on failure.
 */
function open_database(array $config = []): PDO {
    if ($config === []) {
        require_once __DIR__ . '/env.php';
        $readEnv = static fn(string $key): string => (string)($_ENV[$key] ?? getenv($key) ?: '');
        $config = [
            'host' => $readEnv('DB_HOST'),
            'name' => $readEnv('DB_NAME'),
            'user' => $readEnv('DB_USER'),
            'pass' => $readEnv('DB_PASS'),
        ];
    }

    $dbHost = ($config['host'] ?? '') !== '' ? (string)$config['host'] : 'localhost';
    $dbName = ($config['name'] ?? '') !== '' ? (string)$config['name'] : 'wadadli_flare';
    $dbUser = ($config['user'] ?? '') !== '' ? (string)$config['user'] : 'root';
    $dbPass = (string)($config['pass'] ?? '');

    return new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
