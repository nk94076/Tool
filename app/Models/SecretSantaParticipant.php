<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecretSantaParticipant extends Model
{
    protected string $table = 'secret_santa_participants';

    public function findForUser(int $eventId, int $userId): ?array
    {
        $stmt = $this->db()->prepare("SELECT * FROM secret_santa_participants WHERE event_id = :e AND user_id = :u");
        $stmt->execute(['e' => $eventId, 'u' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function optedInUserIds(int $eventId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT ssp.user_id FROM secret_santa_participants ssp
             INNER JOIN users u ON u.id = ssp.user_id
             WHERE ssp.event_id = :e AND ssp.opted_in = 1 AND u.status = 'active'"
        );
        $stmt->execute(['e' => $eventId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function countForEvent(int $eventId): int
    {
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM secret_santa_participants WHERE event_id = :e AND opted_in = 1");
        $stmt->execute(['e' => $eventId]);
        return (int) $stmt->fetchColumn();
    }
}
