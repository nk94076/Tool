<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PushSubscription extends Model
{
    protected string $table = 'push_subscriptions';

    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId);
    }

    public function upsert(int $userId, string $endpoint, string $p256dh, string $auth, ?string $userAgent): void
    {
        $stmt = $this->db()->prepare(
            "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, user_agent, is_active, last_used_at)
             VALUES (:uid, :endpoint, :p256dh, :auth, :ua, 1, NOW())
             ON DUPLICATE KEY UPDATE user_id = :uid2, p256dh = :p256dh2, auth = :auth2, user_agent = :ua2, is_active = 1, last_used_at = NOW()"
        );
        $stmt->execute([
            'uid' => $userId, 'endpoint' => $endpoint, 'p256dh' => $p256dh, 'auth' => $auth, 'ua' => $userAgent,
            'uid2' => $userId, 'p256dh2' => $p256dh, 'auth2' => $auth, 'ua2' => $userAgent,
        ]);
    }

    public function deactivate(string $endpoint): void
    {
        $stmt = $this->db()->prepare("UPDATE push_subscriptions SET is_active = 0 WHERE endpoint = :e");
        $stmt->execute(['e' => $endpoint]);
    }

    public function removeByEndpoint(int $userId, string $endpoint): void
    {
        $stmt = $this->db()->prepare("DELETE FROM push_subscriptions WHERE user_id = :uid AND endpoint = :e");
        $stmt->execute(['uid' => $userId, 'e' => $endpoint]);
    }

    public function activeForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->db()->prepare(
            "SELECT * FROM push_subscriptions WHERE is_active = 1 AND user_id IN ($placeholders)"
        );
        $stmt->execute(array_values($userIds));
        return $stmt->fetchAll();
    }
}
