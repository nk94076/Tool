<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Department extends Model
{
    protected string $table = 'departments';

    public function activeList(): array
    {
        return $this->db()->query("SELECT * FROM departments WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
