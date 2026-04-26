<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function sendVerificationEmail(string $email, string $name, string $code): bool
{
    $resendKey = (string) env('RESEND_API_KEY', '');
    if ($resendKey !== '') {
        return sendVerificationEmailViaResendApi($resendKey, $email, $name, $code);
    }

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
            env('SMTP_FROM', 'noreply@mojchat.local'),
            env('SMTP_FROM_NAME', 'MojChat')
        );
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Potvrdi svoj MojChat nalog';
        $mail->Body    = buildVerificationEmailHtml($name, $code);
        $mail->AltBody = "Zdravo $name,\n\nTvoj kod za potvrdu je: $code\n\nKod važi 15 minuta.\n\nAko se nisi registrovao/la, slobodno ignoriši ovaj email.";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('Mailer error: ' . $e->getMessage());
        return false;
    }
}

function sendVerificationEmailViaResendApi(string $apiKey, string $email, string $name, string $code): bool
{
    $fromEmail = (string) env('SMTP_FROM', '');
    $fromName  = (string) env('SMTP_FROM_NAME', 'MojChat');
    if ($fromEmail === '') {
        error_log('Mailer error: SMTP_FROM must be set when using RESEND_API_KEY.');
        return false;
    }

    if (!function_exists('curl_init')) {
        error_log('Mailer error: cURL extension is required for RESEND_API_KEY sending.');
        return false;
    }

    $payload = [
        'from'    => sprintf('%s <%s>', $fromName, $fromEmail),
        'to'      => [$email],
        'subject' => 'Potvrdi svoj MojChat nalog',
        'html'    => buildVerificationEmailHtml($name, $code),
        'text'    => "Zdravo $name,\n\nTvoj kod za potvrdu je: $code\n\nKod važi 15 minuta.\n\nAko se nisi registrovao/la, slobodno ignoriši ovaj email.",
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $codeHttp = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($body === false) {
        error_log('Mailer error (Resend API): ' . $err);
        return false;
    }

    if ($codeHttp < 200 || $codeHttp >= 300) {
        error_log('Mailer error (Resend API): HTTP ' . $codeHttp . ' body=' . $body);
        return false;
    }

    return true;
}

function buildVerificationEmailHtml(string $name, string $code): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Potvrdi svoj MojChat nalog</title>
</head>
<body style="margin:0;padding:0;background:#0f1117;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f1117;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0" style="background:#1a1d27;border-radius:12px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;max-width:480px;width:100%;">
          <tr>
            <td style="background:#5865f2;padding:28px 32px;text-align:center;">
              <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">💬 MojChat</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 8px;color:#e8eaf0;font-size:16px;">Zdravo <strong>{$safeName}</strong>,</p>
              <p style="margin:0 0 28px;color:#8b92a5;font-size:15px;line-height:1.6;">Hvala na registraciji! Unesi kod ispod da potvrdiš email adresu.</p>

              <div style="background:#252836;border-radius:12px;padding:28px;text-align:center;margin-bottom:28px;">
                <p style="margin:0 0 8px;color:#8b92a5;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Kod za potvrdu</p>
                <p style="margin:0;color:#fff;font-size:42px;font-weight:800;letter-spacing:12px;font-family:monospace;">{$safeCode}</p>
              </div>

              <p style="margin:0 0 8px;color:#8b92a5;font-size:13px;text-align:center;">⏱ Kod važi <strong style="color:#e8eaf0;">15 minuta</strong>.</p>
              <p style="margin:0;color:#8b92a5;font-size:12px;text-align:center;">Ako se nisi registrovao/la, slobodno ignoriši ovaj email.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 32px;border-top:1px solid rgba(255,255,255,0.06);text-align:center;">
              <p style="margin:0;color:#8b92a5;font-size:12px;">© MojChat · Sva prava zadržana</p>
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
