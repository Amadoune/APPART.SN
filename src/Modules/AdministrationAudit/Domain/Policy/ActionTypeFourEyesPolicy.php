<?php

namespace Appart\Modules\AdministrationAudit\Domain\Policy;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;

final readonly class ActionTypeFourEyesPolicy implements FourEyesPolicy
{
    /** @var array<string, true> */
    private array $governedActionTypes;

    /** @param list<ActionType> $actionTypesRequiringApproval */
    public function __construct(array $actionTypesRequiringApproval)
    {
        $governed = [];
        foreach ($actionTypesRequiringApproval as $actionType) {
            $governed[$actionType->value] = true;
        }

        $this->governedActionTypes = $governed;
    }

    public function requiresApproval(ActionType $actionType): bool
    {
        return isset($this->governedActionTypes[$actionType->value]);
    }
}
