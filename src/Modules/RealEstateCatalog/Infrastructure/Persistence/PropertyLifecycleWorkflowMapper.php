<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleStoredState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use RuntimeException;
use Throwable;

final readonly class PropertyLifecycleWorkflowMapper
{
    /** @return array{property_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(PropertyId $propertyId, PropertyLifecycleState $state): array
    {
        $row = ['property_id' => $propertyId->value, 'version' => 1, 'previous_state' => null, 'current_state' => $state->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{property_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(PropertyId $propertyId, PropertyLifecycleTransition $transition, int $version): array
    {
        $row = ['property_id' => $propertyId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): PropertyLifecycleStoredState
    {
        try {
            $snapshot = new PropertyLifecycleStoredState(
                PropertyId::fromString((string) $row['property_id']),
                PropertyLifecycleState::from((string) $row['current_state']),
                (int) $row['version'],
            );
            if ($snapshot->version < 1 || ! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted property lifecycle transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid property lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['property_id'],
            (string) $row['version'],
            $row['previous_state'] === null ? '' : (string) $row['previous_state'],
            (string) $row['current_state'],
            $row['action'] === null ? '' : (string) $row['action'],
        ]));
    }
}
