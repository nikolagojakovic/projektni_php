<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $url = env('DATABASE_URL');
    if (!$url) {
        throw new RuntimeException('DATABASE_URL environment variable is not set.');
    }

    $parts = parse_url($url);
    if ($parts === false) {
        throw new RuntimeException('Invalid DATABASE_URL format.');
    }

    $host = $parts['host'] ?? 'localhost';
    $port = $parts['port'] ?? 5432;
    $dbname = ltrim($parts['path'] ?? '/chatapp', '/');
    $user = $parts['user'] ?? '';
    $pass = $parts['pass'] ?? '';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $pdo = new PDO($dsn, urldecode($user), urldecode($pass), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    runMigrationsIfNeeded($pdo);

    return $pdo;
}

function runMigrationsIfNeeded(PDO $pdo): void
{
    $result = $pdo->query("SELECT to_regclass('public.users')")->fetchColumn();
    if ($result !== null) {
        return;
    }

    $sql = file_get_contents(MIGRATIONS . '/001_schema.sql');
    if ($sql === false) {
        throw new RuntimeException('Could not read migration file.');
    }
    $pdo->exec($sql);
}
