<?php

namespace Tests\Unit\Contracts\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionViolation;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionStatus;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionOutcome;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class AdministrativeActionRegistryContract extends TestCase
{
    private AdministrativeActionRegistryHarness $harness;

    private AdministrativeActionRegistry $registry;

    final protected function setUp(): void
    {
        $this->harness = $this->createHarness();
        $this->registry = $this->harness->freshRegistry();
    }

    abstract protected function createHarness(): AdministrativeActionRegistryHarness;

    final public function test_find_returns_explicit_absence_for_unknown_identity(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
    }

    final public function test_add_then_find_restores_the_observable_aggregate_faithfully(): void
    {
        $expected = $this->harness->minimalAction();
        $this->registry->add($expected);

        $actual = $this->requiredFind($expected);

        self::assertSame($expected->id()->value, $actual->id()->value);
        self::assertSame($expected->authorId()->value, $actual->authorId()->value);
        self::assertSame($expected->targetId()->value, $actual->targetId()->value);
        self::assertSame($expected->actionType()->value, $actual->actionType()->value);
        self::assertSame($expected->status(), $actual->status());
        self::assertSame($expected->version(), $actual->version());
    }

    final public function test_reloaded_aggregate_is_detached(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);

        $this->harness->mutate($loaded);

        self::assertNull($this->requiredFind($action)->reason());
        self::assertSame(0, $this->requiredFind($action)->version());
    }

    final public function test_two_reads_return_independent_instances(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        $first = $this->requiredFind($action);
        $second = $this->requiredFind($action);

        self::assertNotSame($first, $second);
        $this->harness->mutate($first);
        self::assertNull($second->reason());
    }

    final public function test_reloaded_aggregate_contains_no_residual_events(): void
    {
        $action = $this->harness->actionWithHistory();
        self::assertNotEmpty($action->releaseEvents());
        $action = $this->harness->actionWithHistory();
        $this->registry->add($action);

        self::assertSame([], $this->requiredFind($action)->releaseEvents());
    }

    final public function test_duplicate_identity_uses_the_public_port_error(): void
    {
        $this->registry->add($this->harness->minimalAction());

        $this->expectException(AdministrativeActionIdentityConflict::class);
        $this->registry->add($this->harness->minimalAction());
    }

    final public function test_save_succeeds_with_the_expected_version(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);
        $expectedVersion = $loaded->version();
        $this->harness->mutate($loaded);

        $this->registry->save($loaded, $expectedVersion);

        self::assertSame(1, $this->requiredFind($action)->version());
        self::assertNotNull($this->requiredFind($action)->reason());
    }

    final public function test_save_rejects_a_stale_version(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        $winner = $this->requiredFind($action);
        $stale = $this->requiredFind($action);
        $this->harness->mutate($winner);
        $this->harness->mutate($stale);
        $this->registry->save($winner, 0);

        $this->expectException(ConcurrentAdministrativeActionModification::class);
        $this->registry->save($stale, 0);
    }

    final public function test_save_of_an_absent_aggregate_uses_the_contractual_conflict(): void
    {
        $absent = $this->harness->minimalAction($this->harness->distinctId());
        $this->harness->mutate($absent);

        $this->expectException(ConcurrentAdministrativeActionModification::class);
        $this->registry->save($absent, 0);
    }

    final public function test_registry_never_changes_the_version_itself(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        self::assertSame(0, $this->requiredFind($action)->version());
        $this->harness->mutate($action);
        self::assertSame(1, $action->version());

        $this->registry->save($action, 0);

        self::assertSame($action->version(), $this->requiredFind($action)->version());
    }

    final public function test_append_only_history_is_preserved_in_full(): void
    {
        $action = $this->harness->approvedAction();
        $this->registry->add($action);
        $entries = $this->requiredFind($action)->auditEntries();

        self::assertCount(2, $entries);
        self::assertSame([1, 2], array_map(static fn ($entry): int => $entry->sequence, $entries));
        self::assertSame(['administrative_action_recorded', 'administrative_action_approved'], array_map(static fn ($entry): string => $entry->fact, $entries));
        self::assertSame('actor:contract-author', $entries[0]->actorId->value);
        self::assertSame('actor:contract-reviewer', $entries[1]->actorId->value);
    }

    final public function test_approval_decision_audit_and_four_eyes_state_are_restored(): void
    {
        $action = $this->harness->approvedAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);

        self::assertSame(AdministrativeActionStatus::Approved, $loaded->status());
        self::assertSame('31000000-0000-4000-8000-000000000101', $loaded->approval()?->id->value);
        self::assertSame('actor:contract-reviewer', $loaded->approval()?->approverId->value);
        self::assertSame('31000000-0000-4000-8000-000000000102', $loaded->decision()?->id->value);
        self::assertSame(DecisionOutcome::Approved, $loaded->decision()?->outcome);
        self::assertNotSame($loaded->authorId()->value, $loaded->approval()?->approverId->value);
        self::assertCount(2, $loaded->auditEntries());
    }

    final public function test_approved_state_remains_terminal_after_reload(): void
    {
        $action = $this->harness->approvedAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);

        $this->expectException(AdministrativeActionViolation::class);
        $loaded->reject(
            DecisionId::fromString('31000000-0000-4000-8000-000000000199'),
            $loaded->authorId(),
            AuditReason::fromString('A terminal action cannot receive another decision.'),
            new DateTimeImmutable('2026-07-17T10:04:00+00:00'),
        );
    }

    final public function test_rejected_state_remains_terminal_after_reload(): void
    {
        $action = $this->harness->rejectedAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);

        $this->expectException(AdministrativeActionViolation::class);
        $loaded->approve(
            ApprovalId::fromString('31000000-0000-4000-8000-000000000198'),
            DecisionId::fromString('31000000-0000-4000-8000-000000000199'),
            $loaded->authorId(),
            AuditReason::fromString('A terminal action cannot receive another approval.'),
            new DateTimeImmutable('2026-07-17T10:04:00+00:00'),
        );
    }

    final public function test_caller_events_are_not_cleared_before_success(): void
    {
        $action = $this->harness->actionWithHistory();

        $this->registry->add($action);

        self::assertNotEmpty($action->releaseEvents());
    }

    final public function test_previously_produced_events_are_not_replayed_after_reload(): void
    {
        $action = $this->harness->approvedAction();
        $this->registry->add($action);

        self::assertSame([], $this->requiredFind($action)->releaseEvents());
        self::assertSame([], $this->requiredFind($action)->releaseEvents());
    }

    final public function test_failed_write_exposes_no_mutation_and_preserves_caller_events(): void
    {
        $action = $this->harness->minimalAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);
        $this->harness->mutateWithEvent($loaded);
        $this->harness->failNextWrite($this->registry);

        try {
            $this->registry->save($loaded, 0);
            self::fail('The deterministic write failure must be visible.');
        } catch (ConcurrentAdministrativeActionModification) {
            $stored = $this->requiredFind($action);
            self::assertNull($stored->reason());
            self::assertSame(0, $stored->version());
            self::assertNotEmpty($loaded->releaseEvents());
        }
    }

    final public function test_local_child_identities_cannot_be_duplicated_in_the_root(): void
    {
        $action = $this->harness->approvedAction();
        $this->registry->add($action);
        $loaded = $this->requiredFind($action);

        try {
            $loaded->approve(
                $loaded->approval()->id,
                $loaded->decision()->id,
                $loaded->approval()->approverId,
                $loaded->decision()->reason,
                new DateTimeImmutable('2026-07-17T10:04:00+00:00'),
            );
            self::fail('A second approval and decision must be rejected.');
        } catch (AdministrativeActionViolation) {
            self::assertCount(2, $loaded->auditEntries());
            self::assertSame(3, $loaded->version());
        }
    }

    final public function test_each_scenario_starts_with_an_isolated_empty_registry(): void
    {
        self::assertNull($this->registry->find($this->harness->primaryId()));
        self::assertNull($this->registry->find($this->harness->distinctId()));
    }

    private function requiredFind(AdministrativeAction $action): AdministrativeAction
    {
        $loaded = $this->registry->find($action->id());
        self::assertNotNull($loaded);

        return $loaded;
    }
}
