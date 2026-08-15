<?php
/**
 * Deletes expired/used OTP rows. Run daily (or hourly) to keep the
 * otp_verifications table small; nothing here is user-facing.
 *
 * Usage: php cron/cleanup_expired_otp.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Models\Otp;

$deleted = (new Otp())->deleteExpired();
echo "[" . date('Y-m-d H:i:s') . "] Removed $deleted expired/used OTP record(s).\n";
