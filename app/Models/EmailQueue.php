<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmailQueue extends Model
{
    protected string $table = 'email_queue';

    public function enqueue(string $email, string $subject, string $bodyHtml, ?string $templateSlug = null): int
    {
        return $this->insert([
            'recipient_email' => $email,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'template_slug' => $templateSlug,
        ]);
    }

    public function pendingBatch(int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM email_queue WHERE status = 'pending' AND scheduled_at <= NOW()
             AND attempts < max_attempts ORDER BY id ASC LIMIT :lim"
        );
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markProcessing(int $id): void
    {
        $this->update($id, ['status' => 'processing']);
    }

    public function markSent(int $id): void
    {
        $stmt = $this->db()->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $stmt = $this->db()->prepare(
            "UPDATE email_queue SET status = IF(attempts + 1 >= max_attempts, 'failed', 'pending'),
             attempts = attempts + 1, error_message = :err WHERE id = :id"
        );
        $stmt->execute(['err' => mb_substr($error, 0, 490), 'id' => $id]);
    }
}
