<?php
/**
 * Sends "Happy Nth Work Anniversary" wishes on the day of. Run once daily.
 *
 * Usage: php cron/anniversary_notifications.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AnniversaryService;

$count = (new AnniversaryService())->sendTodayWishes();
echo "[" . date('Y-m-d H:i:s') . "] Anniversary wishes processed for $count employee(s).\n";
