<?php
/**
 * Automatically transitions Secret Santa events from "matched" to
 * "completed" once their gift exchange date has passed. Wishlist/message
 * access (ProfileController::recipientWishlist, SecretSantaController) is
 * gated on status === 'matched', so this is what makes access expire
 * automatically after the event per business rule #16.
 *
 * Usage: php cron/expire_secret_santa_access.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

$stmt = Database::connection()->prepare(
    "UPDATE secret_santa_events SET status = 'completed'
     WHERE status = 'matched' AND gift_exchange_date < CURDATE()"
);
$stmt->execute();

echo "[" . date('Y-m-d H:i:s') . "] Expired Secret Santa access for {$stmt->rowCount()} event(s).\n";
