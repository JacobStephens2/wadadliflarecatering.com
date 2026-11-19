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
        $config = [
            'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
            'name' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'wadadli_flare',
            'user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
            'pass' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
        ];
    }

    $dbHost = (string)($config['host'] ?? 'localhost');
    $dbName = (string)($config['name'] ?? 'wadadli_flare');
    $dbUser = (string)($config['user'] ?? 'root');
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
