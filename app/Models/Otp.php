<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Otp extends Model
{
    protected string $table = 'otp_verifications';

    public function latestFor(string $email, string $purpose): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM otp_verifications WHERE email = :e AND purpose = :p ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['e' => $email, 'p' => $purpose]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteExpired(): int
    {
        $stmt = $this->db()->prepare("DELETE FROM otp_verifications WHERE expires_at < NOW() OR is_used = 1");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
