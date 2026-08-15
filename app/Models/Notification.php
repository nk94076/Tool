<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    protected string $table = 'notifications';

    public function forUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim"
        );
        $stmt->bindValue('uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $id, int $userId): bool
    {
        $stmt = $this->db()->prepare(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid"
        );
        return $stmt->execute(['id' => $id, 'uid' => $userId]);
    }

    public function markAllRead(int $userId): bool
    {
        $stmt = $this->db()->prepare(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = :uid AND is_read = 0"
        );
        return $stmt->execute(['uid' => $userId]);
    }
}
