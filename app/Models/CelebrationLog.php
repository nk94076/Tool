<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CelebrationLog extends Model
{
    protected string $table = 'celebration_logs';

    /**
     * Atomically record a send attempt. Returns true if this is the first
     * time (i.e. the caller should actually send), false if already sent
     * (duplicate prevention for cron re-runs).
     */
    public function claim(int $userId, string $eventType, int $eventYear, string $channel): bool
    {
        try {
            $this->insert([
                'user_id' => $userId,
                'event_type' => $eventType,
                'event_year' => $eventYear,
                'channel' => $channel,
            ]);
            return true;
        } catch (\PDOException $e) {
            // Unique constraint violation = already sent.
            return false;
        }
    }
}
