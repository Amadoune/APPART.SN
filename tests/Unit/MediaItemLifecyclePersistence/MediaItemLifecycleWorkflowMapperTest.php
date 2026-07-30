<?php

namespace Tests\Unit\MediaItemLifecyclePersistence;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MediaItemLifecycleWorkflowMapperTest extends TestCase
{
    public function test_mapping_and_checksum_are_deterministic(): void
    {
        $mapper = new MediaItemLifecycleWorkflowMapper;
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000010');
        $transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);

        self::assertSame($mapper->transition($id, $transition, 2), $mapper->transition($id, $transition, 2));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $mapper->transition($id, $transition, 2)['checksum']);
    }

    public function test_snapshot_rejects_a_corrupted_checksum(): void
    {
        $this->expectException(RuntimeException::class);
        (new MediaItemLifecycleWorkflowMapper)->snapshot([
            'media_id' => 'a4600000-0000-4000-8000-000000000010', 'version' => 1, 'previous_state' => null,
            'current_state' => 'active', 'action' => null, 'transition_checksum' => str_repeat('0', 64),
        ]);
    }
}
