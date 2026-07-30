<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\Model\Approval;
use Appart\Modules\AdministrationAudit\Domain\Model\AuditEntry;
use Appart\Modules\AdministrationAudit\Domain\Model\Decision;
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
use Throwable;

final class AdministrativeActionMapper
{
    public function toSnapshot(AdministrativeAction $action): AdministrativeActionSnapshot
    {
        $approval = $action->approval();
        $decision = $action->decision();

        return new AdministrativeActionSnapshot(
            $action->id()->value,
            $action->authorId()->value,
            $action->targetId()->value,
            $action->actionType()->value,
            $action->requiresFourEyes(),
            $this->date($action->lastChangedAt()),
            $action->status()->value,
            $action->reason()?->value,
            $approval === null ? null : new ApprovalSnapshot($approval->id->value, $approval->approverId->value, $this->date($approval->approvedAt)),
            $decision === null ? null : new DecisionSnapshot($decision->id->value, $decision->outcome->value, $decision->decidedBy->value, $decision->reason->value, $this->date($decision->decidedAt)),
            array_map(fn (AuditEntry $entry): AuditEntrySnapshot => new AuditEntrySnapshot($entry->sequence, $entry->fact, $entry->actorId->value, $entry->reason->value, $this->date($entry->recordedAt)), $action->auditEntries()),
            $action->version(),
        );
    }

    public function toAggregate(AdministrativeActionSnapshot $snapshot): AdministrativeAction
    {
        try {
            $status = AdministrativeActionStatus::tryFrom($snapshot->status) ?? throw PersistentAdministrativeActionIntegrity::invalid('status');
            $approval = $snapshot->approval === null ? null : new Approval(
                ApprovalId::fromString($snapshot->approval->id),
                ActorId::fromString($snapshot->approval->approverId),
                $this->parseDate($snapshot->approval->approvedAt),
            );
            $decision = $snapshot->decision === null ? null : new Decision(
                DecisionId::fromString($snapshot->decision->id),
                DecisionOutcome::tryFrom($snapshot->decision->outcome) ?? throw PersistentAdministrativeActionIntegrity::invalid('decision outcome'),
                ActorId::fromString($snapshot->decision->decidedBy),
                AuditReason::fromString($snapshot->decision->reason),
                $this->parseDate($snapshot->decision->decidedAt),
            );
            $entries = $this->auditEntries($snapshot->auditEntries);
            $this->assertCoherent($snapshot, $status, $approval, $decision, $entries);

            $action = AdministrativeAction::reconstitute(
                AdministrativeActionId::fromString($snapshot->id),
                ActorId::fromString($snapshot->authorId),
                TargetResourceId::fromString($snapshot->targetId),
                ActionType::fromString($snapshot->actionType),
                $snapshot->requiresFourEyes,
                $this->parseDate($snapshot->lastChangedAt),
                $status,
                $snapshot->reason === null ? null : AuditReason::fromString($snapshot->reason),
                $approval,
                $decision,
                $entries,
                $snapshot->version,
            );

            if ($action->releaseEvents() !== []) {
                throw PersistentAdministrativeActionIntegrity::invalid('events');
            }

            return $action;
        } catch (PersistentAdministrativeActionIntegrity $error) {
            throw $error;
        } catch (Throwable $error) {
            throw PersistentAdministrativeActionIntegrity::invalid('snapshot');
        }
    }

    /** @param list<AuditEntrySnapshot> $snapshots
     * @return list<AuditEntry>
     */
    private function auditEntries(array $snapshots): array
    {
        $entries = [];
        foreach ($snapshots as $index => $snapshot) {
            if ($snapshot->sequence !== $index + 1) {
                throw PersistentAdministrativeActionIntegrity::invalid('audit history order');
            }
            $entries[] = AuditEntry::record($snapshot->sequence, $snapshot->fact, ActorId::fromString($snapshot->actorId), AuditReason::fromString($snapshot->reason), $this->parseDate($snapshot->recordedAt));
        }

        return $entries;
    }

    /** @param list<AuditEntry> $entries */
    private function assertCoherent(AdministrativeActionSnapshot $snapshot, AdministrativeActionStatus $status, ?Approval $approval, ?Decision $decision, array $entries): void
    {
        $expectedEntries = match ($status) {
            AdministrativeActionStatus::Draft => 0,
            AdministrativeActionStatus::Recorded, AdministrativeActionStatus::PendingApproval => 1,
            AdministrativeActionStatus::Approved, AdministrativeActionStatus::Rejected => 2,
        };
        $coherent = count($entries) === $expectedEntries
            && ($status !== AdministrativeActionStatus::Draft || $snapshot->reason === null || $snapshot->version >= 1)
            && (($status === AdministrativeActionStatus::Approved) === ($approval !== null))
            && (($status === AdministrativeActionStatus::Approved || $status === AdministrativeActionStatus::Rejected) === ($decision !== null))
            && ($approval === null || $decision?->outcome === DecisionOutcome::Approved)
            && ($status !== AdministrativeActionStatus::PendingApproval || $snapshot->requiresFourEyes)
            && ($snapshot->version >= 0);

        if (! $coherent) {
            throw PersistentAdministrativeActionIntegrity::invalid('state');
        }

        $childIds = array_filter([$approval?->id->value, $decision?->id->value]);
        if (count($childIds) !== count(array_unique($childIds))) {
            throw PersistentAdministrativeActionIntegrity::invalid('child identities');
        }
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\\TH:i:s.uP');
    }

    private function parseDate(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i:s.uP', $date);
        if ($parsed === false || $this->date($parsed) !== $date) {
            throw PersistentAdministrativeActionIntegrity::invalid('date');
        }

        return $parsed;
    }
}
