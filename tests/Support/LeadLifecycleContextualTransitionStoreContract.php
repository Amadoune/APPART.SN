<?php

namespace Tests\Support;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class LeadLifecycleContextualTransitionStoreContract extends TestCase
{
    abstract protected function contextualStore(): LeadLifecycleContextualTransitionStore;

    abstract protected function seedCreated(LeadId $leadId): void;

    public function test_contract_applies_and_replays_identically(): void
    {
        $id = $this->contractLeadId();
        $this->seedCreated($id);
        $store = $this->contextualStore();
        $append = $this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
        self::assertSame(LeadLifecycleContextualWriteResult::Applied, $store->append($append));
        self::assertSame(LeadLifecycleContextualWriteResult::AlreadyApplied, $store->append($append));
    }

    public function test_contract_detects_context_divergence(): void
    {
        $id = $this->contractLeadId();
        $this->seedCreated($id);
        $store = $this->contextualStore();
        self::assertSame(LeadLifecycleContextualWriteResult::Applied, $store->append($this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        self::assertSame(LeadLifecycleContextualWriteResult::ContextDivergence, $store->append($this->contractAppend($id, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')));
    }

    protected function contractLeadId(): LeadId
    {
        return LeadId::fromString('a4100000-0000-4000-8000-000000000093');
    }

    protected function contractAppend(LeadId $id, string $actor): LeadLifecycleContextualAppend
    {
        return new LeadLifecycleContextualAppend($id, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), 1, new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:00+00:00'))));
    }
}
