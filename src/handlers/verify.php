<?php

declare(strict_types=1);

$path   = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

$pendingEmail = $_SESSION['pending_email'] ?? '';
$errors       = [];
$successMsg   = '';

if ($pendingEmail === '' && $method === 'GET') {
    header('Location: /register');
    exit;
}

// POST /verify/resend
if ($method === 'POST' && $path === '/verify/resend') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Nevažeći zahtev. Pokušaj ponovo.';
    } else {
        if ($pendingEmail === '') {
            header('Location: /register');
            exit;
        }

        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
        $stmt->execute([$pendingEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: /register');
            exit;
        }

        $code   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $pdo->prepare(
            'UPDATE users SET verification_code = ?, verification_expires_at = ?, verify_attempts = 0, verify_locked_until = NULL WHERE id = ?'
        )->execute([$code, $expiry, $user['id']]);

        $emailSent = sendVerificationEmail($pendingEmail, $user['name'], $code);
        if ($emailSent) {
            $successMsg = 'Novi kod je poslat na tvoj email.';
        } else {
            $msg = 'Slanje emaila nije uspelo. Pokušaj ponovo za koji minut.';
            if (env('APP_ENV', 'production') !== 'production') {
                $detail = getMailerLastError();
                if ($detail !== '') {
                    $msg .= ' Detalji: ' . $detail;
                }
            }
            $errors[] = $msg;
        }
    }
}

// POST /verify
if ($method === 'POST' && $path === '/verify') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Nevažeći zahtev. Pokušaj ponovo.';
    } else {
        if ($pendingEmail === '') {
            header('Location: /register');
            exit;
        }

        $inputCode = trim($_POST['code'] ?? '');
        $pdo       = db();

        $stmt = $pdo->prepare(
            'SELECT id, verification_code, verification_expires_at, verify_attempts, verify_locked_until
             FROM users WHERE email = ?'
        );
        $stmt->execute([$pendingEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: /register');
            exit;
        }

        // Check lock
        if ($user['verify_locked_until'] !== null && strtotime($user['verify_locked_until']) > time()) {
            $unlockTime = date('H:i', strtotime($user['verify_locked_until']));
            $errors[] = "Previše neuspelih pokušaja. Pokušaj ponovo posle $unlockTime.";
        } else {
            // Check expiry
            if ($user['verification_expires_at'] === null || strtotime($user['verification_expires_at']) < time()) {
                $errors[] = 'Kod za potvrdu je istekao. Zatraži novi.';
            } elseif (!hash_equals((string) $user['verification_code'], $inputCode)) {
                // Wrong code — increment attempts
                $attempts = (int) $user['verify_attempts'] + 1;
                if ($attempts >= 5) {
                    $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $pdo->prepare(
                        'UPDATE users SET verify_attempts = ?, verify_locked_until = ? WHERE id = ?'
                    )->execute([$attempts, $lockUntil, $user['id']]);
                    $errors[] = 'Previše neuspelih pokušaja. Nalog je zaključan na 15 minuta.';
                } else {
                    $pdo->prepare(
                        'UPDATE users SET verify_attempts = ? WHERE id = ?'
                    )->execute([$attempts, $user['id']]);
                    $remaining = 5 - $attempts;
                    $errors[] = "Pogrešan kod. Imaš još $remaining pokušaj(a).";
                }
            } else {
                // Success
                $pdo->prepare(
                    'UPDATE users SET is_verified = TRUE, verification_code = NULL,
                     verification_expires_at = NULL, verify_attempts = 0, verify_locked_until = NULL
                     WHERE id = ?'
                )->execute([$user['id']]);

                unset($_SESSION['pending_email']);
                header('Location: /login?verified=1', true, 303);
                exit;
            }
        }
    }
}

// Render form
$pageTitle = 'Potvrda emaila — MojChat';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/verify.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
