<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Announcement;
use App\Models\SecretSantaEvent;

final class CalendarController extends Controller
{
    public function page(): void
    {
        $this->requireLogin();
        $this->view('employee/calendar', ['title' => 'Calendar']);
    }

    /**
     * JSON feed of calendar events for the current year. Only permitted
     * information is exposed: birthdays/anniversaries (name + date, no
     * other personal data), company events, and Secret Santa key dates
     * (never assignment data).
     */
    public function feed(): void
    {
        $this->requireLogin();
        $events = [];

        $stmt = Database::connection()->query(
            "SELECT u.full_name, ep.date_of_birth AS d FROM employee_profiles ep
             INNER JOIN users u ON u.id = ep.user_id
             WHERE u.status = 'active' AND ep.date_of_birth IS NOT NULL"
        );
        foreach ($stmt->fetchAll() as $row) {
            $events[] = [
                'title' => $row['full_name'] . "'s Birthday",
                'date' => date('Y') . '-' . date('m-d', strtotime($row['d'])),
                'type' => 'birthday',
            ];
        }

        $stmt = Database::connection()->query(
            "SELECT u.full_name, ep.date_of_joining AS d FROM employee_profiles ep
             INNER JOIN users u ON u.id = ep.user_id
             WHERE u.status = 'active' AND ep.date_of_joining IS NOT NULL"
        );
        foreach ($stmt->fetchAll() as $row) {
            $events[] = [
                'title' => $row['full_name'] . "'s Work Anniversary",
                'date' => date('Y') . '-' . date('m-d', strtotime($row['d'])),
                'type' => 'anniversary',
            ];
        }

        foreach ((new Announcement())->recent(200) as $a) {
            if ($a['event_date']) {
                $events[] = ['title' => $a['title'], 'date' => $a['event_date'], 'type' => 'event'];
            }
        }

        foreach ((new SecretSantaEvent())->allOrdered() as $e) {
            if (in_array($e['status'], ['active', 'registration_closed', 'matched'], true)) {
                $events[] = ['title' => $e['name'] . ' - Registration Deadline', 'date' => $e['registration_deadline'], 'type' => 'secret_santa'];
                $events[] = ['title' => $e['name'] . ' - Gift Exchange', 'date' => $e['gift_exchange_date'], 'type' => 'secret_santa'];
            }
        }

        $this->json(['events' => $events]);
    }
}
