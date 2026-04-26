<?php

declare(strict_types=1);

function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireAuthJson(): void
{
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function currentUser(): array
{
    static $user = null;

    if ($user !== null) {
        return $user;
    }

    if (!isLoggedIn()) {
        return [];
    }

    $stmt = db()->prepare('SELECT id, name, email, is_verified FROM users WHERE id = ?');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $row = $stmt->fetch();

    $user = $row ?: [];
    return $user;
}
