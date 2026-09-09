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
        $perPage = 50;
        $model = new AuditLog();

        $this->view('admin/audit_logs_index', [
            'title' => 'Audit Logs',
            'logs' => $model->search($filters, $page, $perPage),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $model->countFiltered($filters),
            'todayCount' => $model->countToday(),
            'actions' => $model->distinctActions(),
            'filters' => $filters,
        ]);
    }

    public function emailLogs(): void
    {
        $this->requireLogin();
        $logs = (new EmailLog())->recent(200);
        $sentCount = count(array_filter($logs, fn($l) => $l['status'] === 'sent'));

        $this->view('admin/email_logs_index', [
            'title' => 'Email Logs',
            'logs' => $logs,
            'sentCount' => $sentCount,
            'failedCount' => count($logs) - $sentCount,
        ]);
    }
}
