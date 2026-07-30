<?php

namespace Appart\Modules\AdministrationAudit\Domain\Model;

use Appart\Modules\AdministrationAudit\Domain\Event\AbstractAdministrativeActionEvent;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionApproved;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionEvent;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionRecorded;
use Appart\Modules\AdministrationAudit\Domain\Event\AdministrativeActionRejected;
use Appart\Modules\AdministrationAudit\Domain\Event\AuditEntryRecorded;
use Appart\Modules\AdministrationAudit\Domain\Event\FourEyesSatisfied;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionViolation;
use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;
use Appart\Modules\AdministrationAudit\Domain\Policy\FourEyesPolicy;
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

final class AdministrativeAction
{
    private AdministrativeActionStatus $status = AdministrativeActionStatus::Draft;

    private ?AuditReason $reason = null;

    private ?Approval $approval = null;

    private ?Decision $decision = null;

    /** @var list<AuditEntry> */
    private array $auditEntries = [];

    /** @var list<AdministrativeActionEvent> */
    private array $events = [];

    private int $version = 0;

    private function __construct(
        private readonly AdministrativeActionId $id,
        private readonly ActorId $authorId,
        private readonly TargetResourceId $targetId,
        private readonly ActionType $actionType,
        private readonly bool $requiresFourEyes,
        private DateTimeImmutable $lastChangedAt,
    ) {}

    public static function initiate(AdministrativeActionId $id, ActorId $authorId, TargetResourceId $targetId, ActionType $actionType, FourEyesPolicy $fourEyesPolicy, DateTimeImmutable $initiatedAt): self
    {
        return new self($id, $authorId, $targetId, $actionType, $fourEyesPolicy->requiresApproval($actionType), $initiatedAt);
    }

    /** @param list<AuditEntry> $auditEntries */
    public static function reconstitute(AdministrativeActionId $id, ActorId $authorId, TargetResourceId $targetId, ActionType $actionType, bool $requiresFourEyes, DateTimeImmutable $lastChangedAt, AdministrativeActionStatus $status, ?AuditReason $reason, ?Approval $approval, ?Decision $decision, array $auditEntries, int $version): self
    {
        if ($version < 0) {
            throw InvalidAuditValue::field('version');
        }
        $action = new self($id, $authorId, $targetId, $actionType, $requiresFourEyes, $lastChangedAt);
        $action->status = $status;
        $action->reason = $reason;
        $action->approval = $approval;
        $action->decision = $decision;
        $action->auditEntries = $auditEntries;
        $action->version = $version;

        return $action;
    }

    public function addReason(AuditReason $reason, DateTimeImmutable $at): void
    {
        $this->guardDraft();
        $this->guardTime($at);
        if ($this->reason !== null) {
            throw AdministrativeActionViolation::reasonAlreadyPresent();
        }
        $this->reason = $reason;
        $this->changed($at);
    }

    public function record(DateTimeImmutable $at): void
    {
        $this->guardDraft();
        $this->guardTime($at);
        $reason = $this->reason ?? throw AdministrativeActionViolation::missingReason();
        $this->status = $this->requiresFourEyes ? AdministrativeActionStatus::PendingApproval : AdministrativeActionStatus::Recorded;
        $this->recordEvent(new AdministrativeActionRecorded($this->id, $this->authorId, $this->targetId, $this->actionType, $at));
        $this->addAuditEntry('administrative_action_recorded', $this->authorId, $reason, $at);
        $this->changed($at);
    }

    public function approve(ApprovalId $approvalId, DecisionId $decisionId, ActorId $approverId, AuditReason $reason, DateTimeImmutable $at): void
    {
        $this->guardPendingApproval();
        $this->guardTime($at);
        if ($this->authorId->equals($approverId)) {
            throw AdministrativeActionViolation::selfApproval();
        }
        // Official model: option A. The independent approver is also the final decision-maker.
        $this->approval = new Approval($approvalId, $approverId, $at);
        $this->decision = new Decision($decisionId, DecisionOutcome::Approved, $approverId, $reason, $at);
        $this->status = AdministrativeActionStatus::Approved;
        $this->recordEvent(new AdministrativeActionApproved($this->id, $approvalId, $decisionId, $approverId, $at));
        $this->recordEvent(new FourEyesSatisfied($this->id, $this->authorId, $approverId, $at));
        $this->addAuditEntry('administrative_action_approved', $approverId, $reason, $at);
        $this->changed($at);
    }

    public function reject(DecisionId $decisionId, ActorId $reviewerId, AuditReason $reason, DateTimeImmutable $at): void
    {
        $this->guardPendingApproval();
        $this->guardTime($at);
        if ($this->authorId->equals($reviewerId)) {
            throw AdministrativeActionViolation::selfRejection();
        }
        $this->decision = new Decision($decisionId, DecisionOutcome::Rejected, $reviewerId, $reason, $at);
        $this->status = AdministrativeActionStatus::Rejected;
        $this->recordEvent(new AdministrativeActionRejected($this->id, $decisionId, $reviewerId, $at));
        $this->addAuditEntry('administrative_action_rejected', $reviewerId, $reason, $at);
        $this->changed($at);
    }

    public function id(): AdministrativeActionId
    {
        return $this->id;
    }

    public function authorId(): ActorId
    {
        return $this->authorId;
    }

    public function targetId(): TargetResourceId
    {
        return $this->targetId;
    }

    public function actionType(): ActionType
    {
        return $this->actionType;
    }

    public function requiresFourEyes(): bool
    {
        return $this->requiresFourEyes;
    }

    public function lastChangedAt(): DateTimeImmutable
    {
        return $this->lastChangedAt;
    }

    public function reason(): ?AuditReason
    {
        return $this->reason;
    }

    public function status(): AdministrativeActionStatus
    {
        return $this->status;
    }

    public function approval(): ?Approval
    {
        return $this->approval;
    }

    public function decision(): ?Decision
    {
        return $this->decision;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<AuditEntry> */
    public function auditEntries(): array
    {
        return $this->auditEntries;
    }

    /** @return list<AdministrativeActionEvent> */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function guardDraft(): void
    {
        if ($this->status !== AdministrativeActionStatus::Draft) {
            throw AdministrativeActionViolation::invalidState();
        }
    }

    private function guardPendingApproval(): void
    {
        if (! $this->requiresFourEyes) {
            throw AdministrativeActionViolation::approvalNotRequired();
        } if ($this->status !== AdministrativeActionStatus::PendingApproval) {
            throw AdministrativeActionViolation::invalidState();
        }
    }

    private function guardTime(DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw InvalidAuditValue::field('event_time');
        }
    }

    private function changed(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    private function recordEvent(AdministrativeActionEvent $event): void
    {
        if ($event instanceof AbstractAdministrativeActionEvent) {
            $event->stamp($this->version + 1, count($this->events) + 1);
        }
        $this->events[] = $event;
    }

    private function addAuditEntry(string $fact, ActorId $actorId, AuditReason $reason, DateTimeImmutable $at): void
    {
        $sequence = count($this->auditEntries) + 1;
        $this->auditEntries[] = AuditEntry::record($sequence, $fact, $actorId, $reason, $at);
        $this->recordEvent(new AuditEntryRecorded($this->id, $sequence, $fact, $actorId, $reason, $at));
    }
}
