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
     * Queue for background delivery via cron/email_queue_worker.php.
     * Preferred for bulk/non-urgent mail (birthday wishes, announcements).
     */
    public static function queue(string $toEmail, string $subject, string $bodyHtml, ?string $templateSlug = null): void
    {
        (new EmailQueue())->enqueue($toEmail, $subject, $bodyHtml, $templateSlug);
    }
}
