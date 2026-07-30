<?php

namespace App\Application\AdministrativeActionLifecycleEventIntegration;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleTransitionRequest;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionLifecycleAtomicEventRequest
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public AdministrativeActionLifecycleAction $action,
        public AdministrativeActionTransitionExecutionContext $context,
        public AdministrativeActionHistoricalMirrorOccurredAt $recordedAt,
    ) {}

    public function transitionRequest(): AdministrativeActionLifecycleTransitionRequest
    {
        return new AdministrativeActionLifecycleTransitionRequest(
            $this->actionId,
            $this->action,
            $this->context,
        );
    }
}
