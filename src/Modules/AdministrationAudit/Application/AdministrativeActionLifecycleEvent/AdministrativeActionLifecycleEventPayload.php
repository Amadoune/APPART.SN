<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEventPayload
{
    public function __construct(
        public AdministrativeActionLifecycleEventId $eventId,
        public AdministrativeActionId $actionId,
        public string $transition,
        public AdministrativeActionLifecycleState $previousState,
        public AdministrativeActionLifecycleState $currentState,
        public AdministrativeActionLifecycleAction $action,
        public int $version,
        public int $occurredVersion,
    ) {
        if ($version !== 1
            || $occurredVersion < 1
            || $transition !== implode('>', [$previousState->value, $action->value, $currentState->value])) {
            throw new InvalidArgumentException('Invalid Administrative Action Lifecycle event payload.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'aggregateType' => 'AdministrativeActionLifecycle',
            'administrativeActionId' => $this->actionId->value,
            'transition' => $this->transition,
            'previousState' => $this->previousState->value,
            'currentState' => $this->currentState->value,
            'action' => $this->action->value,
            'version' => $this->version,
            'occurredVersion' => $this->occurredVersion,
        ];
    }
}
