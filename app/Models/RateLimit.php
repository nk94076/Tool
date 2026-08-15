<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Simple DB-backed fixed-window rate limiter (works across multiple PHP-FPM
 * workers without needing Redis/APCu).
 */
final class RateLimit extends Model
{
    protected string $table = 'rate_limits';

    /**
     * @return bool true if the action is allowed, false if the limit was hit.
     */
    public function attempt(string $key, string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $db = $this->db();
        $stmt = $db->prepare("SELECT * FROM rate_limits WHERE `key` = :k AND action = :a LIMIT 1 FOR UPDATE");

        $db->beginTransaction();
        try {
            $stmt->execute(['k' => $key, 'a' => $action]);
            $row = $stmt->fetch();

            $now = new \DateTime();

            if (!$row) {
                $db->prepare("INSERT INTO rate_limits (`key`, action, attempts, window_started_at) VALUES (:k, :a, 1, NOW())")
                    ->execute(['k' => $key, 'a' => $action]);
                $db->commit();
                return true;
            }

            $windowStart = new \DateTime($row['window_started_at']);
            $elapsed = $now->getTimestamp() - $windowStart->getTimestamp();

            if ($elapsed > $windowSeconds) {
                $db->prepare("UPDATE rate_limits SET attempts = 1, window_started_at = NOW() WHERE id = :id")
                    ->execute(['id' => $row['id']]);
                $db->commit();
                return true;
            }

            if ((int) $row['attempts'] >= $maxAttempts) {
                $db->commit();
                return false;
            }

            $db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE id = :id")
                ->execute(['id' => $row['id']]);
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
