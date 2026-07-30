<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use RuntimeException;
use Throwable;

final readonly class ListingPublicationWorkflowMapper
{
    /** @return array{listing_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(ListingId $listingId, ListingPublicationState $state): array
    {
        $row = ['listing_id' => $listingId->value, 'version' => 1, 'previous_state' => null, 'current_state' => $state->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{listing_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(ListingId $listingId, ListingPublicationTransition $transition, int $version): array
    {
        $row = ['listing_id' => $listingId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function snapshot(array $row): ListingPublicationStoredState
    {
        try {
            $snapshot = new ListingPublicationStoredState(
                ListingId::fromString((string) $row['listing_id']),
                ListingPublicationState::from((string) $row['current_state']),
                (int) $row['version'],
            );
            if ($snapshot->version < 1 || ! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted listing publication workflow transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid listing publication workflow persistence row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            (string) $row['listing_id'],
            (string) $row['version'],
            $row['previous_state'] === null ? '' : (string) $row['previous_state'],
            (string) $row['current_state'],
            $row['action'] === null ? '' : (string) $row['action'],
        ]));
    }
}
