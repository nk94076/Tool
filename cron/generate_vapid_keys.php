<?php
/**
 * One-time helper: generates a VAPID key pair for Web Push and prints
 * the values to paste into .env. Not a scheduled cron job — run manually
 * once during setup.
 *
 * Usage: php cron/generate_vapid_keys.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$keys = VAPID::createVapidKeys();

echo "Add these to your .env file:\n\n";
echo "VAPID_PUBLIC_KEY={$keys['publicKey']}\n";
echo "VAPID_PRIVATE_KEY={$keys['privateKey']}\n";
