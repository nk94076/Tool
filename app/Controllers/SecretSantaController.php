<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\SecretSantaEvent;
use App\Models\SecretSantaParticipant;
use App\Models\SecretSantaPreference;
use App\Models\SecretSantaAssignment;
use App\Models\SecretSantaMessage;
use App\Models\User;
use App\Services\SecretSantaService;
use App\Services\AuditService;
use App\Services\NotificationService;

final class SecretSantaController extends Controller
{
    // =================================================================
    // Employee-facing
    // =================================================================

    public function index(): void
    {
        $this->requireLogin();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();
        $participant = null;
        $myRecipient = null;
        $preferences = (new SecretSantaPreference())->findForUser($userId);

        if ($event) {
            $participant = (new SecretSantaParticipant())->findForUser((int) $event['id'], $userId);
            if ($event['status'] === 'matched' && $participant && $participant['opted_in']) {
                $myRecipient = (new SecretSantaAssignment())->recipientFor((int) $event['id'], $userId);
            }
        }

        $this->view('employee/secret_santa', [
            'title' => 'Secret Santa',
            'event' => $event,
            'participant' => $participant,
            'myRecipient' => $myRecipient,
            'preferences' => $preferences,
            'messages' => $myRecipient ? (new SecretSantaMessage())->forSender((int) $myRecipient['assignment_id']) : [],
        ]);
    }

    public function optIn(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();

        if (!$event || !in_array($event['status'], ['active'], true)) {
            set_flash('error', 'There is no open Secret Santa registration right now.');
            $this->redirect('/secret-santa');
        }
        if (strtotime($event['registration_deadline']) < time()) {
            set_flash('error', 'The registration deadline has passed.');
            $this->redirect('/secret-santa');
        }

        $user = Auth::user();
        if ($user['status'] !== 'active' && !$event['allow_inactive_employees']) {
            set_flash('error', 'Only active employees may participate in this event.');
            $this->redirect('/secret-santa');
        }

        $model = new SecretSantaParticipant();
        $existing = $model->findForUser((int) $event['id'], $userId);
        if ($existing) {
            $model->update((int) $existing['id'], ['opted_in' => 1, 'opted_in_at' => date('Y-m-d H:i:s'), 'opted_out_at' => null]);
        } else {
            $model->insert(['event_id' => $event['id'], 'user_id' => $userId]);
        }

        AuditService::log('secret_santa.opted_in', $userId, 'event', null, (string) $event['id']);
        set_flash('success', 'You have joined the Secret Santa event! Don\'t forget to fill in your gift preferences.');
        $this->redirect('/secret-santa');
    }

    public function optOut(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();
        if (!$event || $event['status'] !== 'active') {
            $this->redirect('/secret-santa');
        }

        $model = new SecretSantaParticipant();
        $existing = $model->findForUser((int) $event['id'], $userId);
        if ($existing) {
            $model->update((int) $existing['id'], ['opted_in' => 0, 'opted_out_at' => date('Y-m-d H:i:s')]);
            AuditService::log('secret_santa.opted_out', $userId, 'event', null, (string) $event['id']);
        }

        set_flash('success', 'You have opted out of this Secret Santa event.');
        $this->redirect('/secret-santa');
    }

    public function updatePreferences(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) Auth::id();

        (new SecretSantaPreference())->upsert($userId, [
            'things_i_like' => trim((string) $this->input('things_i_like', '')) ?: null,
            'things_i_dislike' => trim((string) $this->input('things_i_dislike', '')) ?: null,
            'favourite_categories' => trim((string) $this->input('favourite_categories', '')) ?: null,
            'favourite_colours' => trim((string) $this->input('favourite_colours', '')) ?: null,
            'preferred_brands' => trim((string) $this->input('preferred_brands', '')) ?: null,
            'wishlist' => trim((string) $this->input('wishlist', '')) ?: null,
            'budget_preference' => trim((string) $this->input('budget_preference', '')) ?: null,
            'additional_note' => trim((string) $this->input('additional_note', '')) ?: null,
        ]);

        set_flash('success', 'Your Secret Santa preferences have been saved.');
        $this->redirect('/secret-santa');
    }

    /**
     * Recipient's wishlist — visible ONLY to their assigned Secret Santa,
     * and only while the event is still active (access expires after).
     */
    public function recipientWishlist(): void
    {
        $this->requireLogin();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();

        if (!$event || $event['status'] !== 'matched' || $event['status'] === 'completed') {
            (new \App\Core\Router())->abort(403);
        }

        $recipient = (new SecretSantaAssignment())->recipientFor((int) $event['id'], $userId);
        if (!$recipient) {
            (new \App\Core\Router())->abort(403);
        }

        $prefs = (new SecretSantaPreference())->findForUser((int) $recipient['recipient_id']);

        $this->view('employee/secret_santa_wishlist', [
            'title' => 'Your Recipient\'s Wishlist',
            'recipient' => $recipient,
            'preferences' => $prefs,
        ]);
    }

    public function sendMessage(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();
        if (!$event || !in_array($event['status'], ['matched'], true)) {
            (new \App\Core\Router())->abort(403);
        }

        $recipient = (new SecretSantaAssignment())->recipientFor((int) $event['id'], $userId);
        if (!$recipient) {
            (new \App\Core\Router())->abort(403);
        }

        $message = trim((string) $this->input('message', ''));
        if ($message === '' || mb_strlen($message) > 1000) {
            set_flash('error', 'Message must not be empty and under 1000 characters.');
            $this->redirect('/secret-santa');
        }

        (new SecretSantaMessage())->send((int) $event['id'], (int) $recipient['assignment_id'], $userId, $message);

        NotificationService::notify((int) $recipient['recipient_id'], 'secret_santa_message', 'You have a new anonymous Secret Santa message!', null, '/secret-santa/inbox');

        set_flash('success', 'Message sent anonymously to your recipient.');
        $this->redirect('/secret-santa');
    }

    /**
     * Recipient's inbox — messages from their (unidentified) Secret Santa.
     */
    public function inbox(): void
    {
        $this->requireLogin();
        $userId = (int) Auth::id();
        $event = (new SecretSantaEvent())->activeEvent();
        $messages = [];

        if ($event) {
            $assignmentId = (new SecretSantaAssignment())->assignmentIdAsRecipient((int) $event['id'], $userId);
            if ($assignmentId) {
                $messages = (new SecretSantaMessage())->forRecipient($assignmentId);
                (new SecretSantaMessage())->markRead($assignmentId);
            }
        }

        $this->view('employee/secret_santa_inbox', ['title' => 'Secret Santa Inbox', 'messages' => $messages]);
    }

    // =================================================================
    // Admin-facing
    // =================================================================

    public function adminIndex(): void
    {
        $this->requireLogin();
        $this->view('admin/secret_santa_index', [
            'title' => 'Secret Santa Events',
            'events' => (new SecretSantaEvent())->allOrdered(),
        ]);
    }

    public function adminCreate(): void
    {
        $this->requireLogin();
        $this->view('admin/secret_santa_form', ['title' => 'Create Secret Santa Event', 'event' => null]);
    }

    public function adminStore(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $data = $this->collectEventInput();
        $errors = $this->validateEvent($data);
        if (!empty($errors)) {
            set_flash('error', implode(' ', $errors));
            $this->redirect('/admin/secret-santa/create');
        }

        $data['created_by'] = Auth::id();
        $id = (new SecretSantaEvent())->insert($data);
        AuditService::log('secret_santa.event_created', null, 'event', null, $data['name']);

        set_flash('success', 'Secret Santa event created.');
        $this->redirect('/admin/secret-santa');
    }

    public function adminEdit(array $params): void
    {
        $this->requireLogin();
        $event = (new SecretSantaEvent())->find((int) $params['id']);
        if (!$event) {
            (new \App\Core\Router())->abort(404);
        }
        $this->view('admin/secret_santa_form', ['title' => 'Edit Secret Santa Event', 'event' => $event]);
    }

    public function adminUpdate(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new SecretSantaEvent();
        $event = $model->find((int) $params['id']);
        if (!$event) {
            (new \App\Core\Router())->abort(404);
        }
        if (in_array($event['status'], ['matched', 'completed'], true)) {
            set_flash('error', 'This event has already been matched and can no longer be edited.');
            $this->redirect('/admin/secret-santa');
        }

        $data = $this->collectEventInput();
        $errors = $this->validateEvent($data);
        if (!empty($errors)) {
            set_flash('error', implode(' ', $errors));
            $this->redirect('/admin/secret-santa/' . $event['id'] . '/edit');
        }

        $model->update((int) $event['id'], $data);
        AuditService::log('secret_santa.event_updated', null, 'event', $event['name'], $data['name']);

        set_flash('success', 'Event updated.');
        $this->redirect('/admin/secret-santa');
    }

    private function collectEventInput(): array
    {
        return [
            'name' => trim((string) $this->input('name', '')),
            'event_year' => (int) $this->input('event_year', date('Y')),
            'registration_deadline' => $this->input('registration_deadline', ''),
            'gift_exchange_date' => $this->input('gift_exchange_date', ''),
            'min_budget' => (float) $this->input('min_budget', 0),
            'max_budget' => (float) $this->input('max_budget', 0),
            'rules' => trim((string) $this->input('rules', '')) ?: null,
            'allow_inactive_employees' => $this->input('allow_inactive_employees') === '1' ? 1 : 0,
            'avoid_previous_year_pairing' => $this->input('avoid_previous_year_pairing', '1') === '1' ? 1 : 0,
            'status' => $this->input('status', 'draft'),
        ];
    }

    private function validateEvent(array $data): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Event name is required.';
        }
        if (!$data['registration_deadline'] || !$data['gift_exchange_date']) {
            $errors[] = 'Registration deadline and gift exchange date are required.';
        } elseif (strtotime($data['registration_deadline']) > strtotime($data['gift_exchange_date'])) {
            $errors[] = 'Registration deadline must be before the gift exchange date.';
        }
        if ($data['max_budget'] < $data['min_budget']) {
            $errors[] = 'Maximum budget must be greater than or equal to minimum budget.';
        }
        return $errors;
    }

    public function adminCloseRegistration(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new SecretSantaEvent();
        $event = $model->find((int) $params['id']);
        if ($event && $event['status'] === 'active') {
            $model->update((int) $event['id'], ['status' => 'registration_closed']);
            AuditService::log('secret_santa.registration_closed', null, 'event', null, (string) $event['id']);
        }
        set_flash('success', 'Registration closed.');
        $this->redirect('/admin/secret-santa');
    }

    public function adminGenerateMatching(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new SecretSantaEvent();
        $event = $model->find((int) $params['id']);
        if (!$event) {
            (new \App\Core\Router())->abort(404);
        }

        try {
            (new SecretSantaService())->generateMatching($event);
            $model->update((int) $event['id'], ['status' => 'matched', 'matched_at' => date('Y-m-d H:i:s'), 'matched_by' => Auth::id()]);
            AuditService::log('secret_santa.matching_generated', null, 'event', null, (string) $event['id']);

            $participantIds = (new SecretSantaParticipant())->optedInUserIds((int) $event['id']);
            NotificationService::notifyMany($participantIds, 'secret_santa_matched', 'Secret Santa matching is ready!', 'Log in to see your recipient.', '/secret-santa');

            set_flash('success', 'Matching generated and locked successfully.');
        } catch (\RuntimeException $e) {
            set_flash('error', $e->getMessage());
        }

        $this->redirect('/admin/secret-santa');
    }

    /**
     * Emergency reveal of the FULL mapping. Deliberately not exposed by
     * default: requires re-authentication (password) on this same request,
     * shows an explicit warning in the UI, and is fully audit logged.
     */
    public function adminEmergencyRevealForm(array $params): void
    {
        $this->requireLogin();
        $event = (new SecretSantaEvent())->find((int) $params['id']);
        if (!$event) {
            (new \App\Core\Router())->abort(404);
        }
        $this->view('admin/secret_santa_reveal', ['title' => 'Emergency Reveal', 'event' => $event]);
    }

    public function adminEmergencyReveal(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $event = (new SecretSantaEvent())->find((int) $params['id']);
        if (!$event) {
            (new \App\Core\Router())->abort(404);
        }

        $password = (string) $this->input('password', '');
        $currentUser = (new User())->find((int) Auth::id());
        if (!password_verify($password, $currentUser['password_hash'])) {
            set_flash('error', 'Re-authentication failed. Incorrect password.');
            $this->redirect('/admin/secret-santa/' . $event['id'] . '/reveal');
        }

        $mapping = (new SecretSantaAssignment())->fullMappingForEvent((int) $event['id']);
        (new SecretSantaAssignment())->markRevealed((int) $event['id'], (int) Auth::id());
        AuditService::log('secret_santa.emergency_reveal', null, 'event', null, (string) $event['id']);

        $this->view('admin/secret_santa_reveal_result', [
            'title' => 'Secret Santa Mapping (Emergency Reveal)',
            'event' => $event,
            'mapping' => $mapping,
        ]);
    }
}
