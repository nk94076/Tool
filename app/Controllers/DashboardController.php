<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\SecretSantaEvent;
use App\Models\SecretSantaParticipant;
use App\Models\SecretSantaAssignment;
use App\Services\PermissionService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();

        if (Auth::isSuperAdmin() || PermissionService::userHas((int) $user['id'], 'dashboard.view') && $this->isManagementUser($user)) {
            $this->adminDashboard($user);
            return;
        }

        $this->employeeDashboard($user);
    }

    private function isManagementUser(array $user): bool
    {
        $roles = array_column((new User())->roles((int) $user['id']), 'slug');
        return array_intersect($roles, ['super_admin', 'admin', 'manager']) !== [];
    }

    private function adminDashboard(array $user): void
    {
        $userModel = new User();
        $profileModel = new EmployeeProfile();

        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');
        $today = date('Y-m-d');

        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'counts' => $userModel->counts(),
            'todaysBirthdays' => $profileModel->birthdaysOn($today),
            'tomorrowsBirthdays' => $profileModel->birthdaysOn($tomorrow),
            'todaysAnniversaries' => $profileModel->anniversariesOn($today),
            'tomorrowsAnniversaries' => $profileModel->anniversariesOn($tomorrow),
            'upcomingEvents' => (new Announcement())->upcoming(5),
        ]);
    }

    private function employeeDashboard(array $user): void
    {
        $profile = (new EmployeeProfile())->findByUser((int) $user['id']);
        $today = date('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        $activeEvent = (new SecretSantaEvent())->activeEvent();
        $mySecretSanta = null;
        if ($activeEvent && $activeEvent['status'] === 'matched') {
            $participant = (new SecretSantaParticipant())->findForUser((int) $activeEvent['id'], (int) $user['id']);
            if ($participant && $participant['opted_in']) {
                $mySecretSanta = (new SecretSantaAssignment())->recipientFor((int) $activeEvent['id'], (int) $user['id']);
            }
        }

        $this->view('employee/dashboard', [
            'title' => 'Dashboard',
            'profile' => $profile,
            'todaysBirthdays' => (new EmployeeProfile())->birthdaysOn($today),
            'tomorrowsBirthdays' => (new EmployeeProfile())->birthdaysOn($tomorrow),
            'notifications' => (new Notification())->forUser((int) $user['id'], 5),
            'activeEvent' => $activeEvent,
            'mySecretSanta' => $mySecretSanta,
            'announcements' => (new Announcement())->recent(5),
        ]);
    }
}
