<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\CelebrationLog;
use App\Models\User;
use App\Models\EmailTemplate;

final class AnniversaryService
{
    public function sendTomorrowReminders(): int
    {
        if (!setting('anniversary_reminder_enabled', true)) {
            return 0;
        }
        $tomorrow = new \DateTime('+1 day');
        $people = (new EmployeeProfile())->anniversariesOn($tomorrow->format('Y-m-d'));
        return $this->process($people, 'anniversary_reminder', $tomorrow, fn($name, $ord) => "Tomorrow is {$name}'s {$ord} Work Anniversary 🎉");
    }

    public function sendTodayWishes(): int
    {
        if (!setting('anniversary_reminder_enabled', true)) {
            return 0;
        }
        $today = new \DateTime();
        $people = (new EmployeeProfile())->anniversariesOn($today->format('Y-m-d'));
        return $this->process($people, 'anniversary_today', $today, fn($name, $ord) => "Happy {$ord} Work Anniversary {$name}!");
    }

    private function yearsCompleted(string $joiningDate, \DateTime $onDate): ?int
    {
        $joined = \DateTime::createFromFormat('Y-m-d', $joiningDate);
        if (!$joined || $joined > $onDate) {
            return null; // invalid/future joining date — never celebrate these
        }
        $years = (int) $joined->diff($onDate)->y;
        return $years > 0 ? $years : null;
    }

    private function ordinal(int $n): string
    {
        if (in_array($n % 100, [11, 12, 13], true)) {
            return $n . 'th';
        }
        return $n . match ($n % 10) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
        };
    }

    private function process(array $people, string $eventType, \DateTime $onDate, callable $titleFn): int
    {
        if (empty($people)) {
            return 0;
        }

        $celebrationLog = new CelebrationLog();
        $userModel = new User();
        $year = (int) date('Y');
        $allActiveIds = array_map('intval', array_column($userModel->where('status', 'active'), 'id'));
        $count = 0;

        foreach ($people as $person) {
            $years = $this->yearsCompleted($person['date_of_joining'], $onDate);
            if ($years === null) {
                continue; // skip invalid/future joining dates
            }
            $ordinal = $this->ordinal($years);
            $title = $titleFn($person['full_name'], $ordinal);
            $recipients = array_values(array_diff($allActiveIds, [(int) $person['id']]));
            $count++;

            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'in_app')) {
                NotificationService::notifyMany($recipients, $eventType, $title, null, '/directory', false);
            }
            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'push')) {
                PushService::sendToUsers($recipients, $title, '', '/directory');
            }
            if ($celebrationLog->claim((int) $person['id'], $eventType, $year, 'email') && setting('email_notifications_enabled', true)) {
                $this->emailCelebrants($eventType, $person, $years, $recipients);
            }
        }

        return $count;
    }

    private function emailCelebrants(string $eventType, array $person, int $years, array $recipientUserIds): void
    {
        $template = (new EmailTemplate())->findBySlug($eventType);
        if (!$template || !$template['is_active']) {
            return;
        }
        $vars = [
            'employee_name' => $person['full_name'],
            'years_completed' => (string) $years,
            'event_date' => date('d M Y'),
            'joining_date' => format_date($person['date_of_joining']),
        ];
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
