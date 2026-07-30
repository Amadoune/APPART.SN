<?php

namespace Tests\Unit\ProfessionalStatusPersistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PHPUnit\Framework\TestCase;

final class ProfessionalStatusWorkflowMapperTest extends TestCase
{
    public function test_initial_and_transition_rows_are_deterministic(): void
    {
        $mapper = new ProfessionalStatusWorkflowMapper;
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000010');
        $transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);

        self::assertSame($mapper->initial($id), $mapper->initial($id));
        self::assertSame($mapper->transition($id, $transition, 2), $mapper->transition($id, $transition, 2));
        self::assertSame(64, strlen($mapper->transition($id, $transition, 2)['checksum']));
    }

    public function test_snapshot_rejects_checksum_divergence(): void
    {
        $this->expectException(\RuntimeException::class);
        (new ProfessionalStatusWorkflowMapper)->snapshot(['professional_id' => 'a4500000-0000-4000-8000-000000000010', 'version' => 1, 'previous_state' => null, 'current_state' => 'active', 'action' => null, 'transition_checksum' => str_repeat('0', 64)]);
    }
}
