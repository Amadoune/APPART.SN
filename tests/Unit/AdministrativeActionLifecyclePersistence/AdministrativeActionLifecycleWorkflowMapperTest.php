<?php

namespace Tests\Unit\AdministrativeActionLifecyclePersistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AdministrativeActionLifecycleWorkflowMapperTest extends TestCase
{
    public function test_enrollment_and_transition_mapping_are_mechanical_and_deterministic(): void
    {
        $id = AdministrativeActionId::fromString('a47b0000-0000-4000-8000-000000000001');
        $mapper = new AdministrativeActionLifecycleWorkflowMapper;
        $checkpoint = (new AdministrativeActionEnrollmentCanonicalizer)->checkpoint($id, 1, AdministrativeActionLifecycleState::Draft);
        $enrollment = $mapper->enrollment($checkpoint);
        self::assertSame('enrollment', $enrollment['entry_kind']);
        self::assertSame(1, $enrollment['version']);
        self::assertNull($enrollment['previous_state']);

        $mutation = AdministrativeActionHistoricalMirrorMutation::record(
            $id,
            1,
            new AdministrativeActionLifecycleTransition(
                AdministrativeActionLifecycleState::Draft,
                AdministrativeActionLifecycleState::Recorded,
                AdministrativeActionLifecycleAction::Record,
            ),
            ActorId::fromString('actor:contract-author'),
            AuditReason::fromString('Fixed contract evidence for the administrative action.'),
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-17T10:02:00Z')),
        );
        $transition = $mapper->transition($mutation);
        self::assertSame('transition', $transition['entry_kind']);
        self::assertSame(2, $transition['version']);
        self::assertSame('draft', $transition['previous_state']);
        self::assertSame('recorded', $transition['current_state']);
        self::assertSame($mutation->checksum()->value, $transition['mirror_checksum']);
    }

    public function test_snapshot_restores_exact_state_and_detects_checksum_corruption(): void
    {
        $id = AdministrativeActionId::fromString('a47b0000-0000-4000-8000-000000000001');
        $mapper = new AdministrativeActionLifecycleWorkflowMapper;
        $row = $mapper->enrollment(
            (new AdministrativeActionEnrollmentCanonicalizer)->checkpoint($id, 0, AdministrativeActionLifecycleState::Draft),
        );
        $snapshot = $mapper->storedState($row);
        self::assertSame(0, $snapshot->version);
        self::assertSame(AdministrativeActionLifecycleState::Draft, $snapshot->state);

        $row['entry_checksum'] = str_repeat('0', 64);
        $this->expectException(RuntimeException::class);
        $mapper->storedState($row);
    }
}
