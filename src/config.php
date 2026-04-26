<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('SRC', ROOT . '/src');
define('VIEWS', ROOT . '/views');
define('MIGRATIONS', ROOT . '/migrations');

// Load .env file only if vars not already in environment (Railway sets them via process env)
$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $val = getenv($key);
    if ($val === false) {
        return $default;
    }
    return $val;
}

// Error display — hide details in production
if (env('APP_ENV', 'production') === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

require_once SRC . '/db.php';
require_once SRC . '/auth.php';
require_once SRC . '/csrf.php';
require_once SRC . '/mailer.php';
