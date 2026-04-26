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
        $errors[] = 'Invalid request. Please try again.';
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
            $successMsg = 'A new verification code has been sent to your email.';
        } else {
            $errors[] = 'Failed to send email. Please try again shortly.';
        }
    }
}

// POST /verify
if ($method === 'POST' && $path === '/verify') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
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
            $errors[] = "Too many failed attempts. Please try again after $unlockTime.";
        } else {
            // Check expiry
            if ($user['verification_expires_at'] === null || strtotime($user['verification_expires_at']) < time()) {
                $errors[] = 'Verification code has expired. Please request a new one.';
            } elseif (!hash_equals((string) $user['verification_code'], $inputCode)) {
                // Wrong code — increment attempts
                $attempts = (int) $user['verify_attempts'] + 1;
                if ($attempts >= 5) {
                    $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $pdo->prepare(
                        'UPDATE users SET verify_attempts = ?, verify_locked_until = ? WHERE id = ?'
                    )->execute([$attempts, $lockUntil, $user['id']]);
                    $errors[] = 'Too many failed attempts. Your account is locked for 15 minutes.';
                } else {
                    $pdo->prepare(
                        'UPDATE users SET verify_attempts = ? WHERE id = ?'
                    )->execute([$attempts, $user['id']]);
                    $remaining = 5 - $attempts;
                    $errors[] = "Invalid code. You have $remaining attempt(s) remaining.";
                }
            } else {
                // Success
                $pdo->prepare(
                    'UPDATE users SET is_verified = TRUE, verification_code = NULL,
                     verification_expires_at = NULL, verify_attempts = 0, verify_locked_until = NULL
                     WHERE id = ?'
                )->execute([$user['id']]);

                unset($_SESSION['pending_email']);
                header('Location: /login?verified=1');
                exit;
            }
        }
    }
}

// Render form
$pageTitle = 'Verify Email — ChatApp';
$bodyClass = 'auth-page';
ob_start();
require VIEWS . '/verify.php';
$content = ob_get_clean();
require VIEWS . '/layout.php';
