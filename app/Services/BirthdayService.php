<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\CelebrationLog;
use App\Models\User;
use App\Models\EmailTemplate;

/**
 * Birthday reminder/wishing automation. Designed to be run once daily via
 * cron; safe to re-run the same day thanks to celebration_logs dedupe.
 */
final class BirthdayService
{
    public function sendTomorrowReminders(): int
    {
        if (!setting('birthday_reminder_enabled', true)) {
            return 0;
        }
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');
        $people = (new EmployeeProfile())->birthdaysOn($tomorrow);
        return $this->process($people, 'birthday_reminder', fn($name) => "Tomorrow is {$name}'s Birthday 🎂");
    }

    public function sendTodayWishes(): int
    {
        if (!setting('birthday_reminder_enabled', true)) {
            return 0;
        }
        $today = (new \DateTime())->format('Y-m-d');
        $people = (new EmployeeProfile())->birthdaysOn($today);
        return $this->process($people, 'birthday_today', fn($name) => "Today is {$name}'s Birthday 🎉");
    }

    private function process(array $people, string $eventType, callable $titleFn): int
    {
        if (empty($people)) {
            return 0;
        }

        $celebrationLog = new CelebrationLog();
        $userModel = new User();
        $year = (int) date('Y');

        // Notify all active employees (company-wide celebration), excluding the birthday person themselves.
        $allActiveIds = array_map('intval', array_column($userModel->where('status', 'active'), 'id'));

        foreach ($people as $person) {
            $title = $titleFn($person['full_name']);
            $recipients = array_values(array_diff($allActiveIds, [(int) $person['id']]));

            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'in_app')) {
                NotificationService::notifyMany($recipients, $eventType, $title, null, '/directory', false);
            }
            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'push')) {
                PushService::sendToUsers($recipients, $title, '', '/directory');
            }
            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'email') && setting('email_notifications_enabled', true)) {
                $this->emailCelebrants($eventType, $person, $recipients);
            }
        }

        return count($people);
    }

    private function emailCelebrants(string $eventType, array $person, array $recipientUserIds): void
    {
        $template = (new EmailTemplate())->findBySlug($eventType);
        if (!$template || !$template['is_active']) {
            return;
        }
        $vars = ['employee_name' => $person['full_name'], 'event_date' => date('d M Y')];
        $rendered = (new EmailTemplate())->render($template, $vars);

        $userModel = new User();
        foreach ($recipientUserIds as $uid) {
            $user = $userModel->find($uid);
            if ($user) {
                MailService::queue($user['official_email'], $rendered['subject'], $rendered['body_html'], $eventType);
            }
        }
    }
}
