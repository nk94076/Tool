<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecretSantaMessage extends Model
{
    protected string $table = 'secret_santa_messages';

    public function send(int $eventId, int $assignmentId, int $senderUserId, string $message): int
    {
        return $this->insert([
            'event_id' => $eventId,
            'assignment_id' => $assignmentId,
            'sender_user_id' => $senderUserId,
            'message' => $message,
        ]);
    }

    /**
     * Messages visible to the recipient: sender identity is deliberately
     * excluded from the SELECT so it can never leak via this API.
     */
    public function forRecipient(int $assignmentId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT id, message, is_read, created_at FROM secret_santa_messages
             WHERE assignment_id = :aid ORDER BY created_at ASC"
        );
        $stmt->execute(['aid' => $assignmentId]);
        return $stmt->fetchAll();
    }

    public function forSender(int $assignmentId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT id, message, is_read, created_at FROM secret_santa_messages
             WHERE assignment_id = :aid ORDER BY created_at ASC"
        );
        $stmt->execute(['aid' => $assignmentId]);
        return $stmt->fetchAll();
    }

    public function markRead(int $assignmentId): void
    {
        $stmt = $this->db()->prepare("UPDATE secret_santa_messages SET is_read = 1 WHERE assignment_id = :aid");
        $stmt->execute(['aid' => $assignmentId]);
    }
}
