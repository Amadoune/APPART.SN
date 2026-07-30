<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleStoredState;
use RuntimeException;
use Throwable;

final readonly class MediaItemLifecycleWorkflowMapper
{
    /** @return array{media_id:string,version:int,previous_state:null,current_state:string,action:null,checksum:string} */
    public function initial(MediaItemLifecycleId $mediaId): array
    {
        $row = ['media_id' => $mediaId->value, 'version' => 1, 'previous_state' => null, 'current_state' => MediaItemLifecycleState::Active->value, 'action' => null];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @return array{media_id:string,version:int,previous_state:string,current_state:string,action:string,checksum:string} */
    public function transition(MediaItemLifecycleId $mediaId, MediaItemLifecycleTransition $transition, int $version): array
    {
        $row = ['media_id' => $mediaId->value, 'version' => $version, 'previous_state' => $transition->from->value, 'current_state' => $transition->to->value, 'action' => $transition->action->value];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string,mixed> $row */
    public function snapshot(array $row): MediaItemLifecycleStoredState
    {
        try {
            $snapshot = new MediaItemLifecycleStoredState(MediaItemLifecycleId::fromString((string) $row['media_id']), MediaItemLifecycleState::from((string) $row['current_state']), (int) $row['version']);
            if (! hash_equals((string) $row['transition_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Corrupted media item lifecycle transition.');
            }

            return $snapshot;
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid media item lifecycle persistence row.', 0, $error);
        }
    }

    /** @param array<string,mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [(string) $row['media_id'], (string) $row['version'], $row['previous_state'] === null ? '' : (string) $row['previous_state'], (string) $row['current_state'], $row['action'] === null ? '' : (string) $row['action']]));
    }
}
