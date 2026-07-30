<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionLifecycleTransitionRequest
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public AdministrativeActionLifecycleAction $action,
        public AdministrativeActionTransitionExecutionContext $context,
    ) {}
}
