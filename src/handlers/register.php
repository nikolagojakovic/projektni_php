<?php

declare(strict_types=1);

$errors = [];
$formData = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $formData = ['name' => $name, 'email' => $email];

        // Validate
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Name must be 100 characters or fewer.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        } elseif (mb_strlen($email) > 255) {
            $errors['email'] = 'Email must be 255 characters or fewer.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $pdo = db();

            // Check email uniqueness
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'This email is already registered.';
            }
        }

        if (empty($errors)) {
            $pdo = db();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, is_verified, verification_code, verification_expires_at)
                 VALUES (?, ?, ?, FALSE, ?, ?)'
            );
            $stmt->execute([$name, $email, $passwordHash, $code, $expiry]);

            $emailSent = sendVerificationEmail($email, $name, $code);

            $_SESSION['pending_email'] = $email;

            if (!$emailSent) {
                $_SESSION['email_warning'] = 'We could not send the verification email. Please use "Resend code" on the next page.';
            }

            header('Location: /verify');
            exit;
        }
    }
}

// GET or POST with errors — render form
$pageTitle = 'Register — ChatApp';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/register.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
