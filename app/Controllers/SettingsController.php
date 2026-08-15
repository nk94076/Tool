<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SystemSetting;
use App\Services\AuditService;

final class SettingsController extends Controller
{
    private const BOOLEAN_KEYS = [
        'birthday_reminder_enabled', 'anniversary_reminder_enabled',
        'browser_notifications_enabled', 'email_notifications_enabled',
    ];
    private const INTEGER_KEYS = [
        'otp_expiry_minutes', 'otp_resend_cooldown_seconds', 'otp_max_attempts',
        'default_secret_santa_min_budget', 'default_secret_santa_max_budget',
    ];

    public function index(): void
    {
        $this->requireLogin();
        $this->view('admin/settings_index', [
            'title' => 'System Settings',
            'settings' => (new SystemSetting())->allAsMap(),
        ]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new SystemSetting();

        $stringKeys = ['company_name', 'allowed_email_domains', 'timezone', 'notification_time'];
        foreach ($stringKeys as $key) {
            $value = trim((string) $this->input($key, ''));
            if ($value !== '') {
                $model->set($key, $value, 'string', \App\Core\Auth::id());
            }
        }
        foreach (self::INTEGER_KEYS as $key) {
            $value = $this->input($key, null);
            if ($value !== null && $value !== '') {
                $model->set($key, (int) $value, 'integer', \App\Core\Auth::id());
            }
        }
        foreach (self::BOOLEAN_KEYS as $key) {
            $model->set($key, $this->input($key) === '1', 'boolean', \App\Core\Auth::id());
        }

        AuditService::log('system_settings.updated');
        set_flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }
}
