<?php

namespace Tests\Unit\Infrastructure\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionSnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AuditEntrySnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\DecisionSnapshot;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PersistentAdministrativeActionIntegrity;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Contracts\AdministrationAudit\FakeAdministrativeActionRegistryHarness;

final class AdministrativeActionMapperTest extends TestCase
{
    private AdministrativeActionMapper $mapper;

    private FakeAdministrativeActionRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $this->mapper = new AdministrativeActionMapper;
        $this->fixtures = new FakeAdministrativeActionRegistryHarness;
    }

    #[DataProvider('aggregateProvider')]
    public function test_round_trip_preserves_every_observable_property(string $fixture): void
    {
        $expected = $this->fixtures->{$fixture}();
        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        $this->assertSameAggregate($expected, $actual);
        self::assertSame([], $actual->releaseEvents());
    }

    public function test_reason_only_draft_is_preserved(): void
    {
        $expected = $this->fixtures->minimalAction();
        $this->fixtures->mutate($expected);

        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        self::assertSame(1, $actual->version());
        self::assertSame($expected->reason()?->value, $actual->reason()?->value);
        self::assertSame([], $actual->auditEntries());
    }

    public function test_approval_decision_child_identities_dates_and_four_eyes_are_preserved(): void
    {
        $expected = $this->fixtures->approvedAction();
        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        self::assertSame($expected->approval()?->id->value, $actual->approval()?->id->value);
        self::assertEquals($expected->approval()?->approvedAt, $actual->approval()?->approvedAt);
        self::assertSame($expected->decision()?->id->value, $actual->decision()?->id->value);
        self::assertEquals($expected->decision()?->decidedAt, $actual->decision()?->decidedAt);
        self::assertTrue($actual->requiresFourEyes());
        self::assertNotSame($actual->authorId()->value, $actual->approval()?->approverId->value);
    }

    public function test_multiple_audit_entries_keep_exact_order_content_and_dates(): void
    {
        $expected = $this->fixtures->approvedAction();
        $actual = $this->mapper->toAggregate($this->mapper->toSnapshot($expected));

        foreach ($expected->auditEntries() as $index => $entry) {
            $restored = $actual->auditEntries()[$index];
            self::assertSame($entry->sequence, $restored->sequence);
            self::assertSame($entry->fact, $restored->fact);
            self::assertSame($entry->actorId->value, $restored->actorId->value);
            self::assertSame($entry->reason->value, $restored->reason->value);
            self::assertEquals($entry->recordedAt, $restored->recordedAt);
        }
    }

    public function test_next_mutation_after_reconstruction_produces_next_version(): void
    {
        $action = $this->mapper->toAggregate($this->mapper->toSnapshot($this->fixtures->actionWithHistory()));
        self::assertSame(2, $action->version());

        $action->approve(
            ApprovalId::fromString('31000000-0000-4000-8000-000000000151'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000152'),
            ActorId::fromString('actor:mapper-reviewer'),
            AuditReason::fromString('Mapper reconstruction accepts the next valid decision.'),
            new DateTimeImmutable('2026-07-17T10:04:00+00:00'),
        );

        self::assertSame(3, $action->version());
        self::assertNotEmpty($action->releaseEvents());
    }

    public function test_unknown_status_is_refused(): void
    {
        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($this->minimalSnapshot(), status: 'unknown'));
    }

    public function test_unknown_action_type_is_refused(): void
    {
        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($this->minimalSnapshot(), actionType: 'INVALID TYPE'));
    }

    public function test_incomplete_snapshot_is_refused(): void
    {
        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($this->minimalSnapshot(), id: ''));
    }

    public function test_incoherent_history_order_is_refused(): void
    {
        $snapshot = $this->mapper->toSnapshot($this->fixtures->actionWithHistory());
        $entry = $snapshot->auditEntries[0];
        $broken = new AuditEntrySnapshot(2, $entry->fact, $entry->actorId, $entry->reason, $entry->recordedAt);

        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($snapshot, auditEntries: [$broken]));
    }

    public function test_incoherent_version_is_refused(): void
    {
        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($this->minimalSnapshot(), version: -1));
    }

    public function test_invalid_date_is_refused(): void
    {
        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($this->minimalSnapshot(), lastChangedAt: 'not-a-date'));
    }

    public function test_duplicate_child_identities_are_refused(): void
    {
        $snapshot = $this->mapper->toSnapshot($this->fixtures->approvedAction());
        self::assertNotNull($snapshot->approval);
        self::assertNotNull($snapshot->decision);
        $decision = new DecisionSnapshot($snapshot->approval->id, $snapshot->decision->outcome, $snapshot->decision->decidedBy, $snapshot->decision->reason, $snapshot->decision->decidedAt);

        $this->expectException(PersistentAdministrativeActionIntegrity::class);
        $this->mapper->toAggregate($this->copy($snapshot, decision: $decision));
    }

    public static function aggregateProvider(): array
    {
        return [
            'minimal' => ['minimalAction'],
            'recorded' => ['actionWithHistory'],
            'approved' => ['approvedAction'],
            'rejected' => ['rejectedAction'],
        ];
    }

    private function minimalSnapshot(): AdministrativeActionSnapshot
    {
        return $this->mapper->toSnapshot($this->fixtures->minimalAction());
    }

    /** @param list<AuditEntrySnapshot>|null $auditEntries */
    private function copy(
        AdministrativeActionSnapshot $source,
        ?string $id = null,
        ?string $actionType = null,
        ?string $lastChangedAt = null,
        ?string $status = null,
        ?DecisionSnapshot $decision = null,
        ?array $auditEntries = null,
        ?int $version = null,
    ): AdministrativeActionSnapshot {
        return new AdministrativeActionSnapshot(
            $id ?? $source->id,
            $source->authorId,
            $source->targetId,
            $actionType ?? $source->actionType,
            $source->requiresFourEyes,
            $lastChangedAt ?? $source->lastChangedAt,
            $status ?? $source->status,
            $source->reason,
            $source->approval,
            $decision ?? $source->decision,
            $auditEntries ?? $source->auditEntries,
            $version ?? $source->version,
        );
    }

    private function assertSameAggregate(AdministrativeAction $expected, AdministrativeAction $actual): void
    {
        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->authorId()->value, $actual->authorId()->value);
        self::assertSame($expected->targetId()->value, $actual->targetId()->value);
        self::assertSame($expected->actionType()->value, $actual->actionType()->value);
        self::assertSame($expected->requiresFourEyes(), $actual->requiresFourEyes());
        self::assertEquals($expected->lastChangedAt(), $actual->lastChangedAt());
        self::assertSame($expected->status(), $actual->status());
        self::assertSame($expected->reason()?->value, $actual->reason()?->value);
        self::assertEquals($expected->approval(), $actual->approval());
        self::assertEquals($expected->decision(), $actual->decision());
        self::assertEquals($expected->auditEntries(), $actual->auditEntries());
        self::assertSame($expected->version(), $actual->version());
    }
}
