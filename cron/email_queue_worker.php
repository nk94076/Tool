<?php
/**
 * Processes pending rows in email_queue and sends them via SMTP, logging
 * each result to email_logs. Designed to run frequently (e.g. every
 * minute) via cron. Safe to run concurrently thanks to the
 * pending->processing status transition, though a single cron entry is
 * recommended to keep SMTP usage predictable.
 *
 * Usage: php cron/email_queue_worker.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Models\EmailQueue;
use App\Services\MailService;

$queue = new EmailQueue();
$batch = $queue->pendingBatch(50);

$sent = 0;
$failed = 0;

foreach ($batch as $job) {
    $queue->markProcessing((int) $job['id']);
    $ok = MailService::sendNow($job['recipient_email'], $job['subject'], $job['body_html'], $job['template_slug']);
    if ($ok) {
        $queue->markSent((int) $job['id']);
        $sent++;
    } else {
        $queue->markFailed((int) $job['id'], 'SMTP send failed, see email_logs for details.');
        $failed++;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Email queue processed: $sent sent, $failed failed, " . count($batch) . " total.\n";
