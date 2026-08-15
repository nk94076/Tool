<?php
/**
 * Sends "tomorrow is X's Nth work anniversary" reminders. Run once daily.
 * Skips invalid/future joining dates and zero-year (not-yet-completed) cases.
 *
 * Usage: php cron/anniversary_reminders.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AnniversaryService;

$count = (new AnniversaryService())->sendTomorrowReminders();
echo "[" . date('Y-m-d H:i:s') . "] Anniversary reminders processed for $count employee(s).\n";
