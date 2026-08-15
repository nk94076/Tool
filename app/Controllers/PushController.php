<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\PushSubscription;

final class PushController extends Controller
{
    public function vapidPublicKey(): void
    {
        $this->requireLogin();
        $this->json(['public_key' => (string) config('vapid.public_key')]);
    }

    public function subscribe(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $endpoint = $payload['endpoint'] ?? null;
        $keys = $payload['keys'] ?? [];

        if (!$endpoint || empty($keys['p256dh']) || empty($keys['auth'])) {
            $this->json(['error' => 'Invalid subscription payload'], 422);
        }

        (new PushSubscription())->upsert(
            (int) Auth::id(),
            (string) $endpoint,
            (string) $keys['p256dh'],
            (string) $keys['auth'],
            mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)
        );

        $this->json(['success' => true]);
    }

    public function unsubscribe(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $endpoint = $payload['endpoint'] ?? null;
        if ($endpoint) {
            (new PushSubscription())->removeByEndpoint((int) Auth::id(), (string) $endpoint);
        }
        $this->json(['success' => true]);
    }
}
