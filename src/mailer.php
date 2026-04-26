<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function sendVerificationEmail(string $email, string $name, string $code): bool
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = env('SMTP_HOST', 'smtp.mailtrap.io');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('SMTP_USER', '');
        $mail->Password   = env('SMTP_PASS', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) env('SMTP_PORT', '587');

        $mail->setFrom(
            env('SMTP_FROM', 'noreply@chatapp.local'),
            env('SMTP_FROM_NAME', 'ChatApp')
        );
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your ChatApp account';
        $mail->Body    = buildVerificationEmailHtml($name, $code);
        $mail->AltBody = "Hi $name,\n\nYour verification code is: $code\n\nThis code is valid for 15 minutes.\n\nIf you did not register, please ignore this email.";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('Mailer error: ' . $e->getMessage());
        return false;
    }
}

function buildVerificationEmailHtml(string $name, string $code): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify your ChatApp account</title>
</head>
<body style="margin:0;padding:0;background:#0f1117;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f1117;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0" style="background:#1a1d27;border-radius:12px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;max-width:480px;width:100%;">
          <tr>
            <td style="background:#5865f2;padding:28px 32px;text-align:center;">
              <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">💬 ChatApp</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 8px;color:#e8eaf0;font-size:16px;">Hi <strong>{$safeName}</strong>,</p>
              <p style="margin:0 0 28px;color:#8b92a5;font-size:15px;line-height:1.6;">Thanks for signing up! Use the code below to verify your email address.</p>

              <div style="background:#252836;border-radius:12px;padding:28px;text-align:center;margin-bottom:28px;">
                <p style="margin:0 0 8px;color:#8b92a5;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Verification Code</p>
                <p style="margin:0;color:#fff;font-size:42px;font-weight:800;letter-spacing:12px;font-family:monospace;">{$safeCode}</p>
              </div>

              <p style="margin:0 0 8px;color:#8b92a5;font-size:13px;text-align:center;">⏱ This code is valid for <strong style="color:#e8eaf0;">15 minutes</strong>.</p>
              <p style="margin:0;color:#8b92a5;font-size:12px;text-align:center;">If you didn't create an account, you can safely ignore this email.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 32px;border-top:1px solid rgba(255,255,255,0.06);text-align:center;">
              <p style="margin:0;color:#8b92a5;font-size:12px;">© ChatApp · All rights reserved</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
