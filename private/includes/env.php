<?php
/**
 * Environment Variables Loader
 * Uses phpdotenv to load environment variables from .env file
 */

// Only load if not already loaded
if (!defined('ENV_LOADED')) {
    // Determine the .env file path (should be in the private directory)
    $envFile = __DIR__ . '/../.env';
    
    // Load vendor autoload if available (for phpdotenv)
    $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
        
        // Load .env file using phpdotenv
        if (file_exists($envFile)) {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile));
            $dotenv->load();
        }
    } else {
        // Fallback: manual parsing if vendor/autoload.php doesn't exist
        // This allows the site to work even if composer install hasn't been run
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue; // Skip comments and empty lines
                if (strpos($line, '=') === false) continue;
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Set environment variable
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    
    define('ENV_LOADED', true);
}












