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
        $errors[] = 'Nevažeći zahtev. Pokušaj ponovo.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $formData = ['email' => $email];

        if ($email === '' || $password === '') {
            $errors[] = 'Unesi email i lozinku.';
        } else {
            $pdo  = db();
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_verified FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = 'Pogrešan email ili lozinka.';
            } elseif (!$user['is_verified']) {
                $_SESSION['pending_email'] = $email;
                $errors[] = 'Prvo potvrdite vaš email. <a href="/verify">Potvrdi sada</a>.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: /chat', true, 303);
                exit;
            }
        }
    }
}

$pageTitle = 'Prijava — MojChat';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/login.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
