<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SystemSetting extends Model
{
    protected string $table = 'system_settings';

    public function allAsMap(): array
    {
        $rows = $this->db()->query("SELECT setting_key, setting_value, setting_type FROM system_settings")->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $this->cast($row['setting_value'], $row['setting_type']);
        }
        return $map;
    }

    private function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) (int) $value,
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true) ?? [],
            default => $value,
        };
    }

    public function set(string $key, mixed $value, string $type, ?int $updatedBy): void
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $stmt = $this->db()->prepare(
            "INSERT INTO system_settings (setting_key, setting_value, setting_type, updated_by)
             VALUES (:k, :v, :t, :by)
             ON DUPLICATE KEY UPDATE setting_value = :v2, setting_type = :t2, updated_by = :by2"
        );
        $stmt->execute([
            'k' => $key, 'v' => $stored, 't' => $type, 'by' => $updatedBy,
            'v2' => $stored, 't2' => $type, 'by2' => $updatedBy,
        ]);
    }

    public function allowedEmailDomains(): array
    {
        $raw = $this->allAsMap()['allowed_email_domains'] ?? '@adhookmedia.com';
        return array_filter(array_map('trim', explode(',', (string) $raw)));
    }
}
