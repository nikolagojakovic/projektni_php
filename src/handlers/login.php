<?php

declare(strict_types=1);

if (isLoggedIn()) {
    header('Location: /chat');
    exit;
}

$errors   = [];
$formData = ['email' => ''];
$verified = isset($_GET['verified']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $formData = ['email' => $email];

        if ($email === '' || $password === '') {
            $errors[] = 'Please enter your email and password.';
        } else {
            $pdo  = db();
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_verified FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = 'Invalid email or password.';
            } elseif (!$user['is_verified']) {
                // Store pending email so they can go verify
                $_SESSION['pending_email'] = $email;
                $errors[] = 'Please verify your email first. <a href="/verify">Verify now</a>.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: /chat');
                exit;
            }
        }
    }
}

$pageTitle = 'Login — ChatApp';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/login.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
