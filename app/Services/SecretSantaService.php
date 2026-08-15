<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\SecretSantaAssignment;
use App\Models\SecretSantaParticipant;

final class SecretSantaService
{
    public function __construct(
        private SecretSantaAssignment $assignments = new SecretSantaAssignment(),
        private SecretSantaParticipant $participants = new SecretSantaParticipant()
    ) {
    }

    /**
     * Generate and lock the Secret Santa matching for an event.
     *
     * Guarantees: no self-assignment, every participant is santa exactly
     * once and recipient exactly once. Best-effort avoids repeating the
     * previous year's pairing (event settings permitting); if that
     * constraint makes a valid derangement impossible, it is relaxed
     * automatically (the self-assignment rule is never relaxed).
     *
     * @throws \RuntimeException if fewer than 2 participants opted in.
     */
    public function generateMatching(array $event): array
    {
        if ($this->assignments->existsForEvent((int) $event['id'])) {
            throw new \RuntimeException('Assignments already generated for this event and are locked.');
        }

        $userIds = $this->participants->optedInUserIds((int) $event['id']);
        if (count($userIds) < 2) {
            throw new \RuntimeException('At least 2 opted-in participants are required to generate a matching.');
        }

        $forbidden = [];
        if (!empty($event['avoid_previous_year_pairing'])) {
            foreach ($this->assignments->historyPairs((int) $event['event_year'] - 1) as $pair) {
                $forbidden[(int) $pair['santa_user_id']][] = (int) $pair['recipient_user_id'];
            }
        }

        $pairs = $this->attemptDerangement($userIds, $forbidden) ?? $this->attemptDerangement($userIds, []);
        if ($pairs === null) {
            throw new \RuntimeException('Could not generate a valid matching. Try again.');
        }

        $this->assignments->bulkInsert((int) $event['id'], $pairs);
        $this->assignments->archiveToHistory((int) $event['id'], (int) $event['event_year']);

        return $pairs;
    }

    private function attemptDerangement(array $userIds, array $forbidden, int $maxTries = 500): ?array
    {
        for ($try = 0; $try < $maxTries; $try++) {
            $santas = $userIds;
            $recipients = $userIds;
            shuffle($santas);
            shuffle($recipients);

            $pairs = $this->tryAssign($santas, $recipients, $forbidden);
            if ($pairs !== null) {
                return $pairs;
            }
        }
        return null;
    }

    /**
     * Randomized backtracking assignment: for each santa, pick a random
     * still-available recipient that is not themselves and not forbidden.
     */
    private function tryAssign(array $santas, array $recipientPool, array $forbidden): ?array
    {
        $available = $recipientPool;
        $pairs = [];

        foreach ($santas as $santaId) {
            $candidates = array_filter($available, function ($recipientId) use ($santaId, $forbidden) {
                if ($recipientId === $santaId) {
                    return false;
                }
                if (isset($forbidden[$santaId]) && in_array($recipientId, $forbidden[$santaId], true)) {
                    return false;
                }
                return true;
            });

            if (empty($candidates)) {
                return null; // dead end, caller retries with a fresh shuffle
            }

            $chosen = $candidates[array_rand($candidates)];
            $pairs[] = [$santaId, $chosen];
            $available = array_values(array_diff($available, [$chosen]));
        }

        return $pairs;
    }
}
