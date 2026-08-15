<?php
/**
 * Sends "tomorrow is X's birthday" reminders. Run once daily (see README
 * for the recommended crontab). Idempotent: celebration_logs prevents
 * duplicate sends if run twice on the same day.
 *
 * Usage: php cron/birthday_reminders.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\BirthdayService;

$count = (new BirthdayService())->sendTomorrowReminders();
echo "[" . date('Y-m-d H:i:s') . "] Birthday reminders processed for $count employee(s).\n";
