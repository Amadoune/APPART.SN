<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use InvalidArgumentException;

final readonly class AdministrativeActionTransitionExecutionContext
{
    private function __construct(
        public AdministrativeActionTransitionContextVersion $contractVersion,
        public AdministrativeActionExpectedVersion $expectedVersion,
        public ActorId $actor,
        public AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        public AdministrativeActionTransitionDecisionIdentities $decisionIdentities,
        public AuditReason $historicalReason,
        public AdministrativeActionDecisionContext $decisionContext,
    ) {}

    public static function record(
        AdministrativeActionExpectedVersion $expectedVersion,
        ActorId $actor,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        AuditReason $historicalReason,
        AdministrativeActionDecisionContext $decisionContext,
    ): self {
        if (! $actor->equals($decisionContext->authority->author)) {
            throw new InvalidArgumentException('The recording actor must be the certified author.');
        }

        return new self(
            AdministrativeActionTransitionContextVersion::V1,
            $expectedVersion,
            $actor,
            $occurredAt,
            AdministrativeActionTransitionDecisionIdentities::record(),
            $historicalReason,
            $decisionContext,
        );
    }

    public static function approve(
        AdministrativeActionExpectedVersion $expectedVersion,
        ActorId $actor,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        ApprovalId $approvalId,
        DecisionId $decisionId,
        AuditReason $historicalReason,
        AdministrativeActionDecisionContext $decisionContext,
    ): self {
        self::assertDecisionActor($actor, $decisionContext);

        return new self(
            AdministrativeActionTransitionContextVersion::V1,
            $expectedVersion,
            $actor,
            $occurredAt,
            AdministrativeActionTransitionDecisionIdentities::approve($approvalId, $decisionId),
            $historicalReason,
            $decisionContext,
        );
    }

    public static function reject(
        AdministrativeActionExpectedVersion $expectedVersion,
        ActorId $actor,
        AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        DecisionId $decisionId,
        AuditReason $historicalReason,
        AdministrativeActionDecisionContext $decisionContext,
    ): self {
        self::assertDecisionActor($actor, $decisionContext);

        return new self(
            AdministrativeActionTransitionContextVersion::V1,
            $expectedVersion,
            $actor,
            $occurredAt,
            AdministrativeActionTransitionDecisionIdentities::reject($decisionId),
            $historicalReason,
            $decisionContext,
        );
    }

    public function checksum(): AdministrativeActionTransitionContextChecksum
    {
        return AdministrativeActionTransitionContextChecksum::fromString(hash('sha256', implode("\n", [
            (string) $this->contractVersion->value,
            (string) $this->expectedVersion->value,
            $this->actor->value,
            $this->occurredAt->canonical(),
            $this->decisionIdentities->action->value,
            $this->decisionIdentities->approvalId === null ? '' : $this->decisionIdentities->approvalId->value,
            $this->decisionIdentities->decisionId === null ? '' : $this->decisionIdentities->decisionId->value,
            $this->historicalReason->value,
            $this->decisionContext->checksum()->value,
        ])));
    }

    private static function assertDecisionActor(
        ActorId $actor,
        AdministrativeActionDecisionContext $decisionContext,
    ): void {
        if (! $actor->equals($decisionContext->authority->decisionActor)) {
            throw new InvalidArgumentException('The transition actor must be the certified decision actor.');
        }
    }
}
