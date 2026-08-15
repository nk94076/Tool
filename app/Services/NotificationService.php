<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;

final class NotificationService
{
    /**
     * Create an in-app notification and (best-effort) a matching browser push.
     * Email is handled separately by the caller when a template is involved.
     */
    public static function notify(int $userId, string $type, string $title, ?string $body = null, ?string $url = null, bool $push = true): int
    {
        $id = (new Notification())->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);

        if ($push) {
            PushService::sendToUsers([$userId], $title, $body ?? '', $url);
        }

        return $id;
    }

    public static function notifyMany(array $userIds, string $type, string $title, ?string $body = null, ?string $url = null, bool $push = true): void
    {
        foreach ($userIds as $userId) {
            self::notify($userId, $type, $title, $body, $url, false);
        }
        if ($push) {
            PushService::sendToUsers($userIds, $title, $body ?? '', $url);
        }
    }
}
