<?php

namespace Tests\Unit\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextChecksum;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppendInspection;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LeadLifecycleContextualReplayInspectionContractTest extends TestCase
{
    public function test_found_exposes_the_exact_immutable_append(): void
    {
        $snapshot = $this->snapshot();
        $result = LeadLifecycleContextualInspectionResult::found($snapshot);
        self::assertSame(LeadLifecycleContextualInspectionStatus::Found, $result->status);
        self::assertSame($snapshot, $result->snapshot);
        self::assertSame(2, $result->snapshot?->version);
        self::assertTrue((new ReflectionClass($snapshot))->isReadOnly());
        self::assertTrue((new ReflectionClass($result))->isReadOnly());
    }

    public function test_missing_and_corrupted_never_expose_a_snapshot(): void
    {
        $id = $this->id();
        foreach ([LeadLifecycleContextualInspectionResult::missing($id), LeadLifecycleContextualInspectionResult::corrupted($id)] as $result) {
            self::assertNull($result->snapshot);
            self::assertSame($id, $result->leadId);
        }
    }

    public function test_status_set_is_closed(): void
    {
        self::assertSame(['found', 'missing', 'corrupted'], array_column(LeadLifecycleContextualInspectionStatus::cases(), 'value'));
    }

    private function snapshot(): LeadLifecycleContextualAppendInspection
    {
        return new LeadLifecycleContextualAppendInspection($this->id(), 2, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'))), LeadLifecycleContextChecksum::fromString(str_repeat('a', 64)));
    }

    private function id(): LeadId
    {
        return LeadId::fromString('a4100000-0000-4000-8000-000000000094');
    }
}
