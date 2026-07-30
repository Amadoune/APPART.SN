<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

final readonly class AdministrativeActionLifecycleTransition
{
    public function __construct(
        public AdministrativeActionLifecycleState $from,
        public AdministrativeActionLifecycleState $to,
        public AdministrativeActionLifecycleAction $action,
    ) {}
}
