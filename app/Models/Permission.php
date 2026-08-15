<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Permission extends Model
{
    protected string $table = 'permissions';

    public function grouped(): array
    {
        $rows = $this->db()->query("SELECT * FROM permissions ORDER BY group_name, slug")->fetchAll();
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['group_name']][] = $row;
        }
        return $groups;
    }
}
