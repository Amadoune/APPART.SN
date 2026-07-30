<?php

namespace Tests\Unit\LeadLifecycleOrchestration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecycleStoredState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextChecksum;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppendInspection;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicLeadLifecycleOrchestratorTest extends TestCase
{
    #[DataProvider('nominalResults')]
    public function test_maps_every_store_result(LeadLifecycleContextualWriteResult $write, LeadLifecycleOrchestrationStatus $expected): void
    {
        $store = new ControlledStore(LeadLifecyclePersistenceReadResult::found(new LeadLifecycleStoredState($this->id(), LeadLifecycleState::Created, 1)), $write);
        self::assertSame($expected, $this->orchestrator($store, ControlledInspector::missing($this->id()))->execute($this->request())->status);
    }

    public function test_missing_and_corrupted_reads_are_closed(): void
    {
        foreach ([[LeadLifecyclePersistenceReadResult::missing($this->id()), LeadLifecycleOrchestrationStatus::Missing], [LeadLifecyclePersistenceReadResult::corrupted($this->id()), LeadLifecycleOrchestrationStatus::PersistenceCorrupted]] as [$read,$expected]) {
            self::assertSame($expected, $this->orchestrator(new ControlledStore($read), ControlledInspector::missing($this->id()))->execute($this->request())->status);
        }
    }

    public function test_denied_never_appends(): void
    {
        $store = new ControlledStore(LeadLifecyclePersistenceReadResult::found(new LeadLifecycleStoredState($this->id(), LeadLifecycleState::Created, 1)));
        $result = $this->orchestrator($store, ControlledInspector::missing($this->id()))->execute(new LeadLifecycleTransitionRequest($this->id(), LeadLifecycleAction::Close, 1, $this->context()));
        self::assertSame(LeadLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertSame(0, $store->appendCalls);
    }

    public function test_replay_results_come_only_from_inspection(): void
    {
        $read = LeadLifecyclePersistenceReadResult::found(new LeadLifecycleStoredState($this->id(), LeadLifecycleState::Delivered, 2));
        $snapshot = $this->inspection();
        foreach ([[LeadLifecycleContextualInspectionResult::found($snapshot), $this->context(), LeadLifecycleOrchestrationStatus::AlreadyApplied], [LeadLifecycleContextualInspectionResult::found($snapshot), $this->context('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'), LeadLifecycleOrchestrationStatus::ContextDivergence], [LeadLifecycleContextualInspectionResult::corrupted($this->id()), $this->context(), LeadLifecycleOrchestrationStatus::PersistenceCorrupted]] as [$inspection,$context,$expected]) {
            $store = new ControlledStore($read);
            $result = $this->orchestrator($store, new ControlledInspector($inspection))->execute(new LeadLifecycleTransitionRequest($this->id(), LeadLifecycleAction::Deliver, 1, $context));
            self::assertSame($expected, $result->status);
            self::assertSame(0, $store->appendCalls);
        }
    }

    public static function nominalResults(): array
    {
        return [[LeadLifecycleContextualWriteResult::Applied, LeadLifecycleOrchestrationStatus::Applied], [LeadLifecycleContextualWriteResult::AlreadyApplied, LeadLifecycleOrchestrationStatus::AlreadyApplied], [LeadLifecycleContextualWriteResult::VersionConflict, LeadLifecycleOrchestrationStatus::VersionConflict], [LeadLifecycleContextualWriteResult::StateConflict, LeadLifecycleOrchestrationStatus::StateConflict], [LeadLifecycleContextualWriteResult::ContextDivergence, LeadLifecycleOrchestrationStatus::ContextDivergence], [LeadLifecycleContextualWriteResult::Corrupted, LeadLifecycleOrchestrationStatus::PersistenceCorrupted]];
    }

    private function orchestrator($store, $inspector): DeterministicLeadLifecycleOrchestrator
    {
        return new DeterministicLeadLifecycleOrchestrator($store, $inspector, new LeadLifecycleWorkflow);
    }

    private function request(): LeadLifecycleTransitionRequest
    {
        return new LeadLifecycleTransitionRequest($this->id(), LeadLifecycleAction::Deliver, 1, $this->context());
    }

    private function inspection(): LeadLifecycleContextualAppendInspection
    {
        return new LeadLifecycleContextualAppendInspection($this->id(), 2, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), $this->context(), LeadLifecycleContextChecksum::fromString(str_repeat('a', 64)));
    }

    private function context(string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): LeadLifecycleTransitionContext
    {
        return new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')));
    }

    private function id(): LeadId
    {
        return LeadId::fromString('a4100000-0000-4000-8000-000000000096');
    }
}

final class ControlledStore implements LeadLifecycleContextualTransitionStore
{
    public int $appendCalls = 0;

    public function __construct(private LeadLifecyclePersistenceReadResult $read, private LeadLifecycleContextualWriteResult $write = LeadLifecycleContextualWriteResult::Applied) {}

    public function read(LeadId $leadId): LeadLifecyclePersistenceReadResult
    {
        return $this->read;
    }

    public function append(LeadLifecycleContextualAppend $append): LeadLifecycleContextualWriteResult
    {
        $this->appendCalls++;

        return $this->write;
    }
}
final class ControlledInspector implements LeadLifecycleContextualReplayInspector
{
    public function __construct(private LeadLifecycleContextualInspectionResult $result) {}

    public static function missing(LeadId $id): self
    {
        return new self(LeadLifecycleContextualInspectionResult::missing($id));
    }

    public function inspectLatest(LeadId $leadId): LeadLifecycleContextualInspectionResult
    {
        return $this->result;
    }
}
