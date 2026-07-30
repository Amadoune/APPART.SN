<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use InvalidArgumentException;

final readonly class AdministrativeActionHistoricalMirrorMutation
{
    private function __construct(
        public AdministrativeActionHistoricalMirrorContractVersion $contractVersion,
        public AdministrativeActionHistoricalMirrorMutationKind $kind,
        public AdministrativeActionId $actionId,
        public int $expectedHistoricalVersion,
        public AdministrativeActionLifecycleTransition $transition,
        public ActorId $actor,
        public AuditReason $reason,
        public AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        public ?ApprovalId $approvalId,
        public ?DecisionId $decisionId,
    ) {
        if ($expectedHistoricalVersion < 0) {
            throw new InvalidArgumentException('The expected historical version cannot be negative.');
        }
    }

    public static function record(
        AdministrativeActionId $actionId,
        int $expectedHistoricalVersion,
        AdministrativeActionLifecycleTransition $transition,
        ActorId $author,
        AuditReason $reason,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
    ): self {
        self::assertAction($transition, AdministrativeActionLifecycleAction::Record);

        return new self(
            AdministrativeActionHistoricalMirrorContractVersion::V1,
            AdministrativeActionHistoricalMirrorMutationKind::Record,
            $actionId,
            $expectedHistoricalVersion,
            $transition,
            $author,
            $reason,
            $occurredAt,
            null,
            null,
        );
    }

    public static function approve(
        AdministrativeActionId $actionId,
        int $expectedHistoricalVersion,
        AdministrativeActionLifecycleTransition $transition,
        ActorId $decisionActor,
        AuditReason $reason,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        ApprovalId $approvalId,
        DecisionId $decisionId,
    ): self {
        self::assertAction($transition, AdministrativeActionLifecycleAction::Approve);

        return new self(
            AdministrativeActionHistoricalMirrorContractVersion::V1,
            AdministrativeActionHistoricalMirrorMutationKind::Approve,
            $actionId,
            $expectedHistoricalVersion,
            $transition,
            $decisionActor,
            $reason,
            $occurredAt,
            $approvalId,
            $decisionId,
        );
    }

    public static function reject(
        AdministrativeActionId $actionId,
        int $expectedHistoricalVersion,
        AdministrativeActionLifecycleTransition $transition,
        ActorId $decisionActor,
        AuditReason $reason,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        DecisionId $decisionId,
    ): self {
        self::assertAction($transition, AdministrativeActionLifecycleAction::Reject);

        return new self(
            AdministrativeActionHistoricalMirrorContractVersion::V1,
            AdministrativeActionHistoricalMirrorMutationKind::Reject,
            $actionId,
            $expectedHistoricalVersion,
            $transition,
            $decisionActor,
            $reason,
            $occurredAt,
            null,
            $decisionId,
        );
    }

    public function checksum(): AdministrativeActionHistoricalMirrorMutationChecksum
    {
        return AdministrativeActionHistoricalMirrorMutationChecksum::fromString(hash('sha256', implode("\n", [
            (string) $this->contractVersion->value,
            $this->kind->value,
            $this->actionId->value,
            (string) $this->expectedHistoricalVersion,
            $this->transition->from->value,
            $this->transition->action->value,
            $this->transition->to->value,
            $this->actor->value,
            $this->reason->value,
            $this->occurredAt->canonical(),
            $this->approvalId === null ? '' : $this->approvalId->value,
            $this->decisionId === null ? '' : $this->decisionId->value,
        ])));
    }

    private static function assertAction(
        AdministrativeActionLifecycleTransition $transition,
        AdministrativeActionLifecycleAction $expected,
    ): void {
        if ($transition->action !== $expected) {
            throw new InvalidArgumentException('The exact transition does not match the historical mirror mutation kind.');
        }
    }
}
