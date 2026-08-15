<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\EmailLog;

final class AuditLogController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $page = max(1, (int) $this->input('page', 1));
        $filters = ['action' => $this->input('action', '') ?: null];

        $this->view('admin/audit_logs_index', [
            'title' => 'Audit Logs',
            'logs' => (new AuditLog())->search($filters, $page, 50),
            'page' => $page,
        ]);
    }

    public function emailLogs(): void
    {
        $this->requireLogin();
        $this->view('admin/email_logs_index', [
            'title' => 'Email Logs',
            'logs' => (new EmailLog())->recent(200),
        ]);
    }
}
