<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecretSantaAssignment extends Model
{
    protected string $table = 'secret_santa_assignments';

    public function existsForEvent(int $eventId): bool
    {
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM secret_santa_assignments WHERE event_id = :e");
        $stmt->execute(['e' => $eventId]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function bulkInsert(int $eventId, array $pairs): void
    {
        $stmt = $this->db()->prepare(
            "INSERT INTO secret_santa_assignments (event_id, santa_user_id, recipient_user_id) VALUES (:e, :s, :r)"
        );
        foreach ($pairs as [$santaId, $recipientId]) {
            $stmt->execute(['e' => $eventId, 's' => $santaId, 'r' => $recipientId]);
        }
    }

    /**
     * Returns ONLY what the logged-in employee is allowed to see about their
     * own assignment: their recipient's name + preferences. Never includes
     * who their own santa is.
     */
    public function recipientFor(int $eventId, int $santaUserId): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT a.id AS assignment_id, u.id AS recipient_id, u.full_name AS recipient_name,
                    ep.profile_photo_path, ep.department_id, d.name AS department_name, ds.name AS designation_name
             FROM secret_santa_assignments a
             INNER JOIN users u ON u.id = a.recipient_user_id
             LEFT JOIN employee_profiles ep ON ep.user_id = u.id
             LEFT JOIN departments d ON d.id = ep.department_id
             LEFT JOIN designations ds ON ds.id = ep.designation_id
             WHERE a.event_id = :e AND a.santa_user_id = :s"
        );
        $stmt->execute(['e' => $eventId, 's' => $santaUserId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isSantaOf(int $eventId, int $santaUserId, int $assignmentId): bool
    {
        $stmt = $this->db()->prepare(
            "SELECT COUNT(*) FROM secret_santa_assignments WHERE id = :aid AND event_id = :e AND santa_user_id = :s"
        );
        $stmt->execute(['aid' => $assignmentId, 'e' => $eventId, 's' => $santaUserId]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Full mapping — restricted to the emergency-reveal admin flow only.
     * Callers MUST enforce re-authentication + audit logging before calling this.
     */
    public function fullMappingForEvent(int $eventId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT a.*, s.full_name AS santa_name, r.full_name AS recipient_name
             FROM secret_santa_assignments a
             INNER JOIN users s ON s.id = a.santa_user_id
             INNER JOIN users r ON r.id = a.recipient_user_id
             WHERE a.event_id = :e"
        );
        $stmt->execute(['e' => $eventId]);
        return $stmt->fetchAll();
    }

    public function markRevealed(int $eventId, int $revealedBy): void
    {
        $stmt = $this->db()->prepare(
            "UPDATE secret_santa_assignments SET revealed_by_admin = 1, revealed_at = NOW(), revealed_by = :by WHERE event_id = :e"
        );
        $stmt->execute(['by' => $revealedBy, 'e' => $eventId]);
    }

    public function historyPairs(int $priorYear): array
    {
        $stmt = $this->db()->prepare("SELECT santa_user_id, recipient_user_id FROM secret_santa_history WHERE event_year = :y");
        $stmt->execute(['y' => $priorYear]);
        return $stmt->fetchAll();
    }

    public function archiveToHistory(int $eventId, int $eventYear): void
    {
        $stmt = $this->db()->prepare(
            "INSERT INTO secret_santa_history (event_year, santa_user_id, recipient_user_id)
             SELECT :y, santa_user_id, recipient_user_id FROM secret_santa_assignments WHERE event_id = :e"
        );
        $stmt->execute(['y' => $eventYear, 'e' => $eventId]);
    }
}
