<?php

namespace Appart\Modules\Professionals\Infrastructure\Persistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusStoredState;
use RuntimeException;
use Throwable;

final readonly class ProfessionalStatusWorkflowMapper
{
    /** @return array{professional_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(ProfessionalStatusId $professionalId): array
    {
        $row = ['professional_id' => $professionalId->value, 'version' => 1, 'previous_state' => null, 'current_state' => ProfessionalStatusState::Active->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{professional_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(ProfessionalStatusId $professionalId, ProfessionalStatusTransition $transition, int $version): array
    {
        $row = ['professional_id' => $professionalId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string,mixed> $row */
    public function snapshot(array $row): ProfessionalStatusStoredState
    {
        try {
            $snapshot = new ProfessionalStatusStoredState(ProfessionalStatusId::fromString((string) $row['professional_id']), ProfessionalStatusState::from((string) $row['current_state']), (int) $row['version']);
            if (! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted professional status transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid professional status persistence row.', 0, $error);
        }
    }

    /** @param array<string,mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [(string) $row['professional_id'], (string) $row['version'], $row['previous_state'] === null ? '' : (string) $row['previous_state'], (string) $row['current_state'], $row['action'] === null ? '' : (string) $row['action']]));
    }
}
