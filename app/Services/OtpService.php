<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Otp;
use App\Models\EmailTemplate;

final class OtpService
{
    public const RESULT_OK = 'ok';
    public const RESULT_COOLDOWN = 'cooldown';

    private Otp $otpModel;

    public function __construct()
    {
        $this->otpModel = new Otp();
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    public function expiryMinutes(): int
    {
        return (int) setting('otp_expiry_minutes', 10);
    }

    public function resendCooldownSeconds(): int
    {
        return (int) setting('otp_resend_cooldown_seconds', 60);
    }

    public function maxAttempts(): int
    {
        return (int) setting('otp_max_attempts', 5);
    }

    /**
     * @return string self::RESULT_OK or self::RESULT_COOLDOWN
     */
    public function generateAndSend(string $email, string $purpose = 'signup'): string
    {
        $existing = $this->otpModel->latestFor($email, $purpose);
        if ($existing && !$existing['is_used']) {
            $secondsSinceSent = time() - strtotime($existing['last_sent_at']);
            if ($secondsSinceSent < $this->resendCooldownSeconds()) {
                return self::RESULT_COOLDOWN;
            }
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = (new \DateTime())->modify('+' . $this->expiryMinutes() . ' minutes')->format('Y-m-d H:i:s');

        $this->otpModel->insert([
            'email' => $email,
            'otp_hash' => $this->hash($code),
            'purpose' => $purpose,
            'max_attempts' => $this->maxAttempts(),
            'expires_at' => $expiresAt,
            'resend_count' => $existing ? ((int) $existing['resend_count']) + 1 : 0,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $this->sendOtpEmail($email, $code);
        return self::RESULT_OK;
    }

    private function sendOtpEmail(string $email, string $code): void
    {
        $template = (new EmailTemplate())->findBySlug('otp_verification');
        $vars = ['otp_code' => $code, 'expiry_minutes' => (string) $this->expiryMinutes()];

        if ($template && $template['is_active']) {
            $rendered = (new EmailTemplate())->render($template, $vars);
            MailService::sendNow($email, $rendered['subject'], $rendered['body_html'], 'otp_verification');
            return;
        }

        MailService::sendNow(
            $email,
            'Your Adhook Employee Portal verification code',
            "<p>Your one-time verification code is:</p><h2>{$code}</h2><p>This code expires in {$this->expiryMinutes()} minutes. Do not share it with anyone.</p>",
            'otp_verification'
        );
    }

    /**
     * @return array{success: bool, reason?: string}
     */
    public function verify(string $email, string $purpose, string $code): array
    {
        $otp = $this->otpModel->latestFor($email, $purpose);

        if (!$otp || $otp['is_used']) {
            return ['success' => false, 'reason' => 'no_otp'];
        }
        if (strtotime($otp['expires_at']) < time()) {
            return ['success' => false, 'reason' => 'expired'];
        }
        if ((int) $otp['attempts'] >= (int) $otp['max_attempts']) {
            return ['success' => false, 'reason' => 'max_attempts'];
        }

        if (!hash_equals($otp['otp_hash'], $this->hash($code))) {
            $this->otpModel->update((int) $otp['id'], ['attempts' => (int) $otp['attempts'] + 1]);
            return ['success' => false, 'reason' => 'invalid'];
        }

        $this->otpModel->update((int) $otp['id'], ['is_used' => 1]);
        return ['success' => true];
    }
}
