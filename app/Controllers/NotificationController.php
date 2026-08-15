<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function list(): void
    {
        $this->requireLogin();
        $model = new Notification();
        $userId = (int) Auth::id();
        $this->json([
            'notifications' => $model->forUser($userId, 20),
            'unread_count' => $model->unreadCount($userId),
        ]);
    }

    public function markRead(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        (new Notification())->markRead((int) $params['id'], (int) Auth::id());
        $this->json(['success' => true]);
    }

    public function markAllRead(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        (new Notification())->markAllRead((int) Auth::id());
        $this->json(['success' => true]);
    }

    public function page(): void
    {
        $this->requireLogin();
        $userId = (int) Auth::id();
        $model = new Notification();
        $this->view('employee/notifications', [
            'title' => 'Notifications',
            'notifications' => $model->forUser($userId, 100),
        ]);
    }
}
