<?php

declare(strict_types=1);

$errors = [];
$formData = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Nevažeći zahtev. Pokušaj ponovo.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $formData = ['name' => $name, 'email' => $email];

        if ($name === '') {
            $errors['name'] = 'Ime je obavezno.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Ime može imati najviše 100 karaktera.';
        }

        if ($email === '') {
            $errors['email'] = 'Email je obavezan.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Nevažeća email adresa.';
        } elseif (mb_strlen($email) > 255) {
            $errors['email'] = 'Email može imati najviše 255 karaktera.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Lozinka mora imati najmanje 8 karaktera.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Lozinke se ne podudaraju.';
        }

        if (empty($errors)) {
            $pdo = db();

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Ovaj email je već registrovan.';
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
                $_SESSION['email_warning'] = 'Nismo uspeli da pošaljemo email za potvrdu. Koristi dugme "Pošalji ponovo" na sledećoj stranici.';
            }

            header('Location: /verify', true, 303);
            exit;
        }
    }
}

$pageTitle = 'Registracija — MojChat';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/register.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
