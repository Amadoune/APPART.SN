<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionContextualAppend
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public AdministrativeActionLifecycleTransition $transition,
        public AdministrativeActionTransitionExecutionContext $context,
    ) {}

    public function nextVersion(): int
    {
        return $this->context->expectedVersion->next();
    }
}
