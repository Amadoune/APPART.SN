<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;

final readonly class PropertyLifecycleEventPayload
{
    public function __construct(
        public PropertyId $propertyId,
        public PropertyLifecycleState $previousState,
        public PropertyLifecycleState $state,
        public PropertyLifecycleAction $action,
        public int $lifecycleVersion,
    ) {
        if ($lifecycleVersion < 2) {
            throw new InvalidArgumentException('A property lifecycle transition event version must be at least two.');
        }
    }

    /** @return array{propertyId:string,previousState:string,state:string,action:string,lifecycleVersion:int} */
    public function fields(): array
    {
        return [
            'propertyId' => $this->propertyId->value,
            'previousState' => $this->previousState->value,
            'state' => $this->state->value,
            'action' => $this->action->value,
            'lifecycleVersion' => $this->lifecycleVersion,
        ];
    }
}
