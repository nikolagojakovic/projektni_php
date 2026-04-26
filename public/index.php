<?php

declare(strict_types=1);

// Buffer output so redirects still work even if a warning/notice happens.
ob_start();

require_once __DIR__ . '/../src/config.php';

// Secure session cookie settings before session_start
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (env('APP_ENV', 'production') === 'production') {
    ini_set('session.cookie_secure', '1');
}

session_start();

$path   = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

match (true) {
    $path === '/' && $method === 'GET'
        => (function () {
            header('Location: ' . (isLoggedIn() ? '/chat' : '/login'));
            exit;
        })(),

    $path === '/register' && ($method === 'GET' || $method === 'POST')
        => require SRC . '/handlers/register.php',

    $path === '/verify' && ($method === 'GET' || $method === 'POST')
        => require SRC . '/handlers/verify.php',

    $path === '/verify/resend' && $method === 'POST'
        => require SRC . '/handlers/verify.php',

    $path === '/login' && ($method === 'GET' || $method === 'POST')
        => require SRC . '/handlers/login.php',

    $path === '/chat' && $method === 'GET'
        => require SRC . '/handlers/chat.php',

    $path === '/logout' && $method === 'POST'
        => require SRC . '/handlers/logout.php',

    $path === '/api/messages' && ($method === 'GET' || $method === 'POST')
        => require SRC . '/handlers/api_messages.php',

    default => (function () {
        http_response_code(404);
        $pageTitle = '404 — MojChat';
        $bodyClass = 'auth-page';
        ob_start();
        require VIEWS . '/404.php';
        $content = ob_get_clean();
        require VIEWS . '/layout.php';
    })(),
};
