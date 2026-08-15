<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SecretSantaPreference extends Model
{
    protected string $table = 'secret_santa_preferences';

    public function findForUser(int $userId): ?array
    {
        return $this->whereFirst('user_id', $userId);
    }

    public function upsert(int $userId, array $data): void
    {
        $existing = $this->findForUser($userId);
        $data['user_id'] = $userId;
        if ($existing) {
            $this->update((int) $existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }
}
