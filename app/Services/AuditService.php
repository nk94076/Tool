<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Core\Auth;

final class AuditService
{
    public static function log(string $action, ?int $subjectUserId = null, ?string $field = null, mixed $old = null, mixed $new = null): void
    {
        (new AuditLog())->record(
            Auth::id(),
            $subjectUserId,
            $action,
            $field,
            $old !== null ? (string) $old : null,
            $new !== null ? (string) $new : null
        );
    }
}
