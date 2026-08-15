<?php
/**
 * Sends "happy birthday" wishes on the day of. Run once daily.
 * Idempotent via celebration_logs.
 *
 * Usage: php cron/birthday_notifications.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\BirthdayService;

$count = (new BirthdayService())->sendTodayWishes();
echo "[" . date('Y-m-d H:i:s') . "] Birthday wishes processed for $count employee(s).\n";
