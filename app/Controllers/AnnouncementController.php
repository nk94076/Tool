<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Announcement;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\MailService;

final class AnnouncementController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('admin/announcements_index', [
            'title' => 'Announcements',
            'announcements' => (new Announcement())->recent(50),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $title = trim((string) $this->input('title', ''));
        $body = trim((string) $this->input('body', ''));
        $category = $this->input('category', 'general');
        $eventDate = $this->input('event_date', '') ?: null;
        $notifyEmail = $this->input('notify_email') === '1' ? 1 : 0;
        $notifyPush = $this->input('notify_push', '1') === '1' ? 1 : 0;

        if ($title === '' || $body === '') {
            set_flash('error', 'Title and body are required.');
            $this->redirect('/admin/announcements');
        }

        $id = (new Announcement())->insert([
            'title' => $title, 'body' => $body, 'category' => $category, 'event_date' => $eventDate,
            'notify_email' => $notifyEmail, 'notify_push' => $notifyPush, 'created_by' => Auth::id(),
        ]);

        AuditService::log('announcement.created', null, 'announcement', null, $title);

        $activeUserIds = array_map('intval', array_column((new User())->where('status', 'active'), 'id'));
        NotificationService::notifyMany($activeUserIds, 'announcement', $title, mb_substr(strip_tags($body), 0, 200), '/announcements', (bool) $notifyPush);

        if ($notifyEmail && setting('email_notifications_enabled', true)) {
            $userModel = new User();
            foreach ($activeUserIds as $uid) {
                $user = $userModel->find($uid);
                if ($user) {
                    MailService::queue($user['official_email'], $title, '<p>' . nl2br(e($body)) . '</p>', 'announcement');
                }
            }
        }

        set_flash('success', 'Announcement published.');
        $this->redirect('/admin/announcements');
    }

    public function delete(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        (new Announcement())->delete((int) $params['id']);
        AuditService::log('announcement.deleted', null, 'announcement', (string) $params['id']);
        set_flash('success', 'Announcement deleted.');
        $this->redirect('/admin/announcements');
    }

    public function employeeIndex(): void
    {
        $this->requireLogin();
        $this->view('employee/announcements', [
            'title' => 'Announcements',
            'announcements' => (new Announcement())->recent(50),
        ]);
    }
}
