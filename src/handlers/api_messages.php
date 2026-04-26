<?php

declare(strict_types=1);

header('Content-Type: application/json');

requireAuthJson();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $after = max(0, (int) ($_GET['after'] ?? 0));

    $stmt = db()->prepare(
        'SELECT m.id, m.user_id, m.content, m.created_at, u.name AS user_name
         FROM messages m
         JOIN users u ON u.id = m.user_id
         WHERE m.id > ?
         ORDER BY m.id ASC
         LIMIT 50'
    );
    $stmt->execute([$after]);
    $messages = $stmt->fetchAll();

    echo json_encode(['messages' => $messages]);
    exit;
}

if ($method === 'POST') {
    // CSRF — check header first, then body
    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $bodyRaw     = file_get_contents('php://input');
    $body        = json_decode($bodyRaw ?: '{}', true) ?? [];
    $bodyToken   = $body['csrf_token'] ?? '';

    $token = $headerToken !== '' ? $headerToken : $bodyToken;

    if (!validateCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token.']);
        exit;
    }

    $content = trim($body['content'] ?? '');

    if ($content === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Message cannot be empty.']);
        exit;
    }

    if (mb_strlen($content) > 1000) {
        http_response_code(422);
        echo json_encode(['error' => 'Message must be 1000 characters or fewer.']);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    $pdo    = db();

    $stmt = $pdo->prepare(
        'INSERT INTO messages (user_id, content) VALUES (?, ?) RETURNING id, user_id, content, created_at'
    );
    $stmt->execute([$userId, $content]);
    $message = $stmt->fetch();

    // Attach user name
    $user              = currentUser();
    $message['user_name'] = $user['name'] ?? '';

    echo json_encode(['ok' => true, 'message' => $message]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
exit;
