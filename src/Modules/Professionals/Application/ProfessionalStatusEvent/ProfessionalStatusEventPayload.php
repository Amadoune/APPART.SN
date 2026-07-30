<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use InvalidArgumentException;

final readonly class ProfessionalStatusEventPayload
{
    public function __construct(
        public ProfessionalStatusEventId $eventId,
        public ProfessionalStatusId $professionalId,
        public string $transition,
        public ProfessionalStatusState $previousState,
        public ProfessionalStatusState $currentState,
        public ProfessionalStatusAction $action,
        public int $version,
        public int $occurredVersion,
    ) {
        if ($version !== 1 || $occurredVersion < 2 || $transition !== implode('>', [$previousState->value, $action->value, $currentState->value])) {
            throw new InvalidArgumentException('Invalid professional status event payload.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return ['eventId' => $this->eventId->value, 'aggregateType' => 'ProfessionalStatus', 'professionalId' => $this->professionalId->value, 'transition' => $this->transition, 'previousState' => $this->previousState->value, 'currentState' => $this->currentState->value, 'action' => $this->action->value, 'version' => $this->version, 'occurredVersion' => $this->occurredVersion];
    }
}
