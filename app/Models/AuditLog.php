<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function record(
        ?int $actorUserId,
        ?int $subjectUserId,
        string $action,
        ?string $field = null,
        ?string $oldValue = null,
        ?string $newValue = null
    ): void {
        $this->insert([
            'actor_user_id' => $actorUserId,
            'subject_user_id' => $subjectUserId,
            'action' => $action,
            'field_name' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
        ]);
    }

    public function search(array $filters, int $page = 1, int $perPage = 50): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['subject_user_id'])) {
            $where[] = 'al.subject_user_id = :subject_user_id';
            $params['subject_user_id'] = $filters['subject_user_id'];
        }
        if (!empty($filters['action'])) {
            $where[] = 'al.action = :action';
            $params['action'] = $filters['action'];
        }
        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT al.*, actor.full_name AS actor_name, subject.full_name AS subject_name
                FROM audit_logs al
                LEFT JOIN users actor ON actor.id = al.actor_user_id
                LEFT JOIN users subject ON subject.id = al.subject_user_id
                WHERE $whereSql ORDER BY al.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
