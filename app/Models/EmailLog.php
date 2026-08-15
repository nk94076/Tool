<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmailLog extends Model
{
    protected string $table = 'email_logs';

    public function recent(int $limit = 100): array
    {
        $stmt = $this->db()->prepare("SELECT * FROM email_logs ORDER BY id DESC LIMIT :lim");
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
