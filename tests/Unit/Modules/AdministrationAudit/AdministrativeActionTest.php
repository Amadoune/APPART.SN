<?php

namespace Tests\Unit\Modules\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionApproved;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionRecorded;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionRejected;
use Appart\Modules\AdministrationAudit\Domain\Event\AuditEntryRecorded;
use Appart\Modules\AdministrationAudit\Domain\Event\FourEyesSatisfied;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionViolation;
use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Policy\ActionTypeFourEyesPolicy;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionStatus;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionOutcome;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionTest extends TestCase
{
    public function test_it_creates_a_draft_action_with_an_attributable_target(): void
    {
        $a = $this->action();
        self::assertSame(AdministrativeActionStatus::Draft, $a->status());
        self::assertSame('actor:author', $a->authorId()->value);
        self::assertSame('identity:account-42', $a->targetId()->value);
    }

    public function test_it_adds_an_explicit_reason(): void
    {
        $a = $this->action();
        $a->addReason($this->reason(), $this->now());
        self::assertSame($this->reason()->value, $a->reason()?->value);
    }

    public function test_it_rejects_recording_without_a_reason(): void
    {
        $this->expectException(AdministrativeActionViolation::class);
        $this->action()->record($this->now());
    }

    public function test_it_rejects_a_second_reason(): void
    {
        $a = $this->action();
        $a->addReason($this->reason(), $this->now());
        $this->expectException(AdministrativeActionViolation::class);
        $a->addReason($this->reason(), $this->now());
    }

    public function test_it_records_an_action_and_its_audit_proof(): void
    {
        $a = $this->recorded();
        $events = $a->releaseEvents();
        self::assertSame(AdministrativeActionStatus::PendingApproval, $a->status());
        self::assertCount(1, $a->auditEntries());
        self::assertInstanceOf(AdministrativeActionRecorded::class, $events[0]);
        self::assertTrue($events[0]->actionId()->equals($a->id()));
        self::assertTrue($events[0]->authorId->equals($a->authorId()));
        self::assertSame($a->targetId()->value, $events[0]->targetId->value);
        self::assertSame($a->actionType()->value, $events[0]->actionType->value);
        self::assertEquals($this->now(), $events[0]->occurredAt());
        self::assertInstanceOf(AuditEntryRecorded::class, $events[1]);
        self::assertSame(1, $events[1]->sequence);
        self::assertSame('administrative_action_recorded', $events[1]->fact);
        self::assertTrue($events[1]->actorId->equals($a->authorId()));
        self::assertSame($this->reason()->value, $events[1]->reason->value);
        self::assertEquals($this->now(), $events[1]->occurredAt());
    }

    public function test_a_non_sensitive_action_is_recorded_without_approval(): void
    {
        $a = $this->action(false);
        $a->addReason($this->reason(), $this->now());
        $a->record($this->now());
        self::assertSame(AdministrativeActionStatus::Recorded, $a->status());
    }

    public function test_it_approves_with_a_distinct_actor_and_satisfies_four_eyes(): void
    {
        $a = $this->recorded();
        $a->releaseEvents();
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        $events = $a->releaseEvents();
        self::assertSame(AdministrativeActionStatus::Approved, $a->status());
        self::assertSame(DecisionOutcome::Approved, $a->decision()?->outcome);
        self::assertInstanceOf(AdministrativeActionApproved::class, $events[0]);
        self::assertInstanceOf(FourEyesSatisfied::class, $events[1]);
        self::assertInstanceOf(AuditEntryRecorded::class, $events[2]);
    }

    public function test_the_author_cannot_self_approve(): void
    {
        $a = $this->recorded();
        $this->expectException(AdministrativeActionViolation::class);
        $a->approve($this->approvalId(), $this->decisionId(), $a->authorId(), $this->decisionReason(), $this->now());
    }

    public function test_it_rejects_an_action_with_a_distinct_reviewer(): void
    {
        $a = $this->recorded();
        $a->releaseEvents();
        $a->reject($this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        $events = $a->releaseEvents();
        self::assertSame(AdministrativeActionStatus::Rejected, $a->status());
        self::assertInstanceOf(AdministrativeActionRejected::class, $events[0]);
        self::assertTrue($events[0]->actionId()->equals($a->id()));
        self::assertTrue($events[0]->reviewerId->equals($this->reviewer()));
        self::assertSame($a->decision()?->id->value, $events[0]->decisionId->value);
        self::assertEquals($this->now(), $events[0]->occurredAt());
        self::assertInstanceOf(AuditEntryRecorded::class, $events[1]);
        self::assertSame(2, $events[1]->sequence);
        self::assertSame('administrative_action_rejected', $events[1]->fact);
    }

    public function test_the_author_cannot_self_reject(): void
    {
        $a = $this->recorded();
        $this->expectException(AdministrativeActionViolation::class);
        $a->reject($this->decisionId(), $a->authorId(), $this->decisionReason(), $this->now());
    }

    public function test_a_final_decision_cannot_be_repeated(): void
    {
        $a = $this->recorded();
        $a->reject($this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());
        $this->expectException(AdministrativeActionViolation::class);
        $a->reject(DecisionId::fromString('30000000-0000-4000-8000-000000000009'), $this->reviewer(), $this->decisionReason(), $this->now());
    }

    public function test_an_approval_cannot_be_repeated(): void
    {
        $a = $this->recorded();
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());

        $this->expectException(AdministrativeActionViolation::class);
        $a->approve(ApprovalId::fromString('30000000-0000-4000-8000-000000000008'), DecisionId::fromString('30000000-0000-4000-8000-000000000009'), $this->reviewer(), $this->decisionReason(), $this->now());
    }

    public function test_an_approved_action_cannot_be_rejected(): void
    {
        $a = $this->recorded();
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());

        $this->expectException(AdministrativeActionViolation::class);
        $a->reject(DecisionId::fromString('30000000-0000-4000-8000-000000000009'), $this->reviewer(), $this->decisionReason(), $this->now());
    }

    public function test_a_rejected_action_cannot_be_approved(): void
    {
        $a = $this->recorded();
        $a->reject($this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->now());

        $this->expectException(AdministrativeActionViolation::class);
        $a->approve($this->approvalId(), DecisionId::fromString('30000000-0000-4000-8000-000000000009'), $this->reviewer(), $this->decisionReason(), $this->now());
    }

    public function test_audit_entries_are_append_only_sequenced_and_complete(): void
    {
        $a = $this->recorded();
        $firstSnapshot = $a->auditEntries();
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->later());
        $entries = $a->auditEntries();

        self::assertCount(1, $firstSnapshot);
        self::assertCount(2, $entries);
        self::assertSame(1, $entries[0]->sequence);
        self::assertSame('administrative_action_recorded', $entries[0]->fact);
        self::assertTrue($entries[0]->actorId->equals($a->authorId()));
        self::assertSame($this->reason()->value, $entries[0]->reason->value);
        self::assertEquals($this->now(), $entries[0]->recordedAt);
        self::assertSame(2, $entries[1]->sequence);
        self::assertSame('administrative_action_approved', $entries[1]->fact);
        self::assertTrue($entries[1]->actorId->equals($this->reviewer()));
        self::assertSame($this->decisionReason()->value, $entries[1]->reason->value);
        self::assertEquals($this->later(), $entries[1]->recordedAt);
    }

    public function test_external_snapshot_changes_cannot_rewrite_or_remove_audit_entries(): void
    {
        $a = $this->recorded();
        $snapshot = $a->auditEntries();
        array_pop($snapshot);

        self::assertSame([], $snapshot);
        self::assertCount(1, $a->auditEntries());
        self::assertSame('administrative_action_recorded', $a->auditEntries()[0]->fact);
    }

    public function test_a_past_date_cannot_break_audit_chronology(): void
    {
        $a = $this->recorded();

        $this->expectException(InvalidAuditValue::class);
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), new DateTimeImmutable('2026-07-16T11:59:59+00:00'));
    }

    public function test_approval_event_content_matches_option_a_two_person_policy(): void
    {
        $a = $this->recorded();
        $a->releaseEvents();
        $a->approve($this->approvalId(), $this->decisionId(), $this->reviewer(), $this->decisionReason(), $this->later());
        $events = $a->releaseEvents();

        self::assertInstanceOf(AdministrativeActionApproved::class, $events[0]);
        self::assertTrue($events[0]->approverId->equals($a->decision()->decidedBy));
        self::assertSame($this->approvalId()->value, $events[0]->approvalId->value);
        self::assertTrue($events[0]->decisionId === $a->decision()->id);
        self::assertInstanceOf(FourEyesSatisfied::class, $events[1]);
        self::assertTrue($events[1]->authorId->equals($a->authorId()));
        self::assertTrue($events[1]->approverId->equals($this->reviewer()));
    }

    public function test_release_events_empties_the_collection(): void
    {
        $a = $this->recorded();
        self::assertNotEmpty($a->releaseEvents());
        self::assertSame([], $a->releaseEvents());
    }

    public function test_a_refused_operation_produces_no_event(): void
    {
        $a = $this->recorded();
        $a->releaseEvents();
        try {
            $a->approve($this->approvalId(), $this->decisionId(), $a->authorId(), $this->decisionReason(), $this->now());
            self::fail();
        } catch (AdministrativeActionViolation) {
            self::assertSame([], $a->releaseEvents());
        }
    }

    private function recorded(): AdministrativeAction
    {
        $a = $this->action();
        $a->addReason($this->reason(), $this->now());
        $a->record($this->now());

        return $a;
    }

    private function action(bool $fourEyes = true): AdministrativeAction
    {
        $type = ActionType::fromString($fourEyes ? 'role_assignment_review' : 'profile_note');
        $policy = new ActionTypeFourEyesPolicy([ActionType::fromString('role_assignment_review')]);

        return AdministrativeAction::initiate(AdministrativeActionId::fromString('30000000-0000-4000-8000-000000000001'), ActorId::fromString('actor:author'), TargetResourceId::fromString('identity:account-42'), $type, $policy, $this->now());
    }

    private function reason(): AuditReason
    {
        return AuditReason::fromString('Administrative review requested with supporting evidence.');
    }

    private function decisionReason(): AuditReason
    {
        return AuditReason::fromString('Independent reviewer validated the submitted evidence.');
    }

    private function approvalId(): ApprovalId
    {
        return ApprovalId::fromString('30000000-0000-4000-8000-000000000002');
    }

    private function decisionId(): DecisionId
    {
        return DecisionId::fromString('30000000-0000-4000-8000-000000000003');
    }

    private function reviewer(): ActorId
    {
        return ActorId::fromString('actor:reviewer');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }

    private function later(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:01:00+00:00');
    }
}
