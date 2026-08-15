<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Announcement extends Model
{
    protected string $table = 'announcements';

    public function recent(int $limit = 20): array
    {
        $stmt = $this->db()->prepare("SELECT * FROM announcements ORDER BY created_at DESC LIMIT :lim");
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function upcoming(int $limit = 10): array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM announcements WHERE event_date IS NOT NULL AND event_date >= CURDATE()
             ORDER BY event_date ASC LIMIT :lim"
        );
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
