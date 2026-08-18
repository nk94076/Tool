<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailQueue;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

final class MailService
{
    /**
     * Send immediately (used for time-sensitive mail like OTP). Logs result
     * either way; never throws to the caller — failures are logged, not fatal.
     */
    public static function sendNow(string $toEmail, string $subject, string $bodyHtml, ?string $templateSlug = null): bool
    {
        return self::deliver($toEmail, $subject, self::brand($bodyHtml), $templateSlug);
    }

    /**
     * Queue for background delivery via cron/email_queue_worker.php.
     * Preferred for bulk/non-urgent mail (birthday wishes, announcements).
     */
    public static function queue(string $toEmail, string $subject, string $bodyHtml, ?string $templateSlug = null): void
    {
        (new EmailQueue())->enqueue($toEmail, $subject, self::brand($bodyHtml), $templateSlug);
    }

    /**
     * Send a body that has already been through brand() — used by the queue
     * worker, since queue() branded the body before storing it. Sending it
     * through sendNow() there would wrap it a second time.
     */
    public static function sendRaw(string $toEmail, string $subject, string $bodyHtml, ?string $templateSlug = null): bool
    {
        return self::deliver($toEmail, $subject, $bodyHtml, $templateSlug);
    }

    private static function deliver(string $toEmail, string $subject, string $bodyHtml, ?string $templateSlug): bool
    {
        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = (string) config('mail.host');
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) config('mail.username');
            $mailer->Password = (string) config('mail.password');
            $mailer->SMTPSecure = (string) config('mail.encryption');
            $mailer->Port = (int) config('mail.port');
            $mailer->CharSet = 'UTF-8';
            // Bound how long a slow/unreachable SMTP host can block the
            // request — OTP/login/signup send mail synchronously and must
            // not hang indefinitely if SMTP is misconfigured or down.
            $mailer->Timeout = 10;
            $mailer->SMTPKeepAlive = false;

            $mailer->setFrom((string) config('mail.from_email'), (string) config('mail.from_name'));
            $mailer->addAddress($toEmail);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $bodyHtml;
            $mailer->AltBody = strip_tags($bodyHtml);

            $mailer->send();

            (new EmailLog())->insert([
                'recipient_email' => $toEmail,
                'subject' => $subject,
                'template_slug' => $templateSlug,
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        } catch (PHPMailerException | \Throwable $e) {
            app_log('Mail send failed to ' . $toEmail . ': ' . $e->getMessage());
            (new EmailLog())->insert([
                'recipient_email' => $toEmail,
                'subject' => $subject,
                'template_slug' => $templateSlug,
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 490),
            ]);
            return false;
        }
    }

    /**
     * Wrap a template's inner body_html in a branded shell (logo, gradient
     * accent, card, footer) so every outgoing email looks like it came from
     * the portal rather than a bare unstyled paragraph. Table-based markup
     * with inline styles for compatibility with Outlook/Gmail rendering.
     */
    private static function brand(string $innerHtml): string
    {
        $logoUrl = (string) config('app.url') . '/assets/img/logo.png';
        $companyName = (string) (setting('company_name', 'Adhook Media') ?? 'Adhook Media');
        $year = date('Y');

        return <<<HTML
<!doctype html>
<html>
<body style="margin:0;padding:0;background:#f4f5fb;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5fb;padding:32px 16px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
<tr><td align="center">
<table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width:480px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6e8f0;">
<tr><td style="background:linear-gradient(135deg,#5b3df6,#7fd8c9);height:6px;line-height:6px;font-size:0;">&nbsp;</td></tr>
<tr><td align="center" style="padding:28px 24px 4px;">
<img src="{$logoUrl}" alt="{$companyName}" height="32" style="height:32px;width:auto;display:block;border:0;">
</td></tr>
<tr><td align="center" style="padding:0 24px 20px;color:#6b7280;font-size:12px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">Employee Portal</td></tr>
<tr><td style="padding:8px 32px 32px;color:#1f2330;font-size:14.5px;line-height:1.65;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
{$innerHtml}
</td></tr>
<tr><td style="background:#f7f7fb;padding:16px 24px;text-align:center;color:#9aa0b4;font-size:11.5px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;border-top:1px solid #f0f1f7;">
&copy; {$year} {$companyName} &middot; Employee Portal
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
