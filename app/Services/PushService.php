<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

final class PushService
{
    /**
     * Send a browser push notification to every active subscription for the
     * given user IDs. Failures (expired/invalid subscription) are pruned
     * automatically and never bubble up — push is best-effort.
     */
    public static function sendToUsers(array $userIds, string $title, string $body, ?string $url = null): void
    {
        if (!setting('browser_notifications_enabled', true)) {
            return;
        }
        if (empty(config('vapid.public_key')) || empty(config('vapid.private_key'))) {
            return; // Not configured — degrade gracefully.
        }

        $subscriptionModel = new PushSubscription();
        $subs = $subscriptionModel->activeForUsers($userIds);
        if (empty($subs)) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => (string) config('vapid.subject'),
                    'publicKey' => (string) config('vapid.public_key'),
                    'privateKey' => (string) config('vapid.private_key'),
                ],
            ]);
        } catch (\Throwable $e) {
            app_log('WebPush init failed: ' . $e->getMessage());
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/dashboard',
            'icon' => '/assets/img/icon-192.png',
        ]);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'publicKey' => $sub['p256dh'],
                    'authToken' => $sub['auth'],
                ]),
                $payload
            );
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if ($report->isSubscriptionExpired()) {
                    $subscriptionModel->deactivate($endpoint);
                } else {
                    app_log('Push failed for endpoint ' . $endpoint . ': ' . $report->getReason());
                }
            }
        }
    }
}
