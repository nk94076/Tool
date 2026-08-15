<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecretSantaEvent extends Model
{
    protected string $table = 'secret_santa_events';

    public function activeEvent(): ?array
    {
        $stmt = $this->db()->query(
            "SELECT * FROM secret_santa_events WHERE status IN ('active','registration_closed','matched')
             ORDER BY id DESC LIMIT 1"
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allOrdered(): array
    {
        return $this->db()->query("SELECT * FROM secret_santa_events ORDER BY event_year DESC, id DESC")->fetchAll();
    }
}
