<?php
/**
 * Routine housekeeping: prunes old read notifications, stale rate-limit
 * windows, and expired password-reset tokens. Run daily.
 *
 * Usage: php cron/cleanup_stale_data.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

$pdo = Database::connection();

$notif = $pdo->exec("DELETE FROM notifications WHERE is_read = 1 AND read_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
$rate = $pdo->exec("DELETE FROM rate_limits WHERE window_started_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
$reset = $pdo->exec("UPDATE users SET password_reset_token = NULL, password_reset_expires_at = NULL WHERE password_reset_expires_at IS NOT NULL AND password_reset_expires_at < NOW()");

echo "[" . date('Y-m-d H:i:s') . "] Cleanup complete: $notif old notifications, $rate stale rate-limit rows, $reset expired reset tokens.\n";
