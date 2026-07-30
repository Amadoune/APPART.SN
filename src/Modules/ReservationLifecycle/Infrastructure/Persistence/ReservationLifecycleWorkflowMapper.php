<?php

namespace Appart\Modules\ReservationLifecycle\Infrastructure\Persistence;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecycleStoredState;
use RuntimeException;
use Throwable;

final readonly class ReservationLifecycleWorkflowMapper
{
    /** @return array{reservation_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(ReservationId $reservationId, ReservationLifecycleState $state): array
    {
        $row = ['reservation_id' => $reservationId->value, 'version' => 1, 'previous_state' => null, 'current_state' => $state->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{reservation_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(ReservationId $reservationId, ReservationLifecycleTransition $transition, int $version): array
    {
        $row = ['reservation_id' => $reservationId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): ReservationLifecycleStoredState
    {
        try {
            $snapshot = new ReservationLifecycleStoredState(
                ReservationId::fromString((string) $row['reservation_id']),
                ReservationLifecycleState::from((string) $row['current_state']),
                (int) $row['version'],
            );
            if ($snapshot->version < 1 || ! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted reservation lifecycle transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid reservation lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['reservation_id'],
            (string) $row['version'],
            $row['previous_state'] === null ? '' : (string) $row['previous_state'],
            (string) $row['current_state'],
            $row['action'] === null ? '' : (string) $row['action'],
        ]));
    }
}
