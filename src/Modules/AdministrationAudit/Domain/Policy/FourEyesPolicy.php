<?php

namespace Appart\Modules\AdministrationAudit\Domain\Policy;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;

interface FourEyesPolicy
{
    public function requiresApproval(ActionType $actionType): bool;
}
