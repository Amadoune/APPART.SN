<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;

final readonly class AdministrativeActionTransitionDecisionIdentities
{
    private function __construct(
        public AdministrativeActionLifecycleAction $action,
        public ?ApprovalId $approvalId,
        public ?DecisionId $decisionId,
    ) {}

    public static function record(): self
    {
        return new self(AdministrativeActionLifecycleAction::Record, null, null);
    }

    public static function approve(ApprovalId $approvalId, DecisionId $decisionId): self
    {
        return new self(AdministrativeActionLifecycleAction::Approve, $approvalId, $decisionId);
    }

    public static function reject(DecisionId $decisionId): self
    {
        return new self(AdministrativeActionLifecycleAction::Reject, null, $decisionId);
    }
}
