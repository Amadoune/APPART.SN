<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use InvalidArgumentException;

final readonly class LeadLifecycleEventPayload
{
    public function __construct(public LeadLifecycleEventId $eventId, public LeadId $leadId, public string $transition, public LeadLifecycleState $previousState, public LeadLifecycleState $currentState, public LeadLifecycleAction $action, public int $version, public int $occurredVersion)
    {
        if ($version !== 1 || $occurredVersion < 2 || $transition !== implode('>', [$previousState->value, $action->value, $currentState->value])) {
            throw new InvalidArgumentException('Invalid Lead event payload.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return ['eventId' => $this->eventId->value, 'aggregateType' => 'LeadLifecycle', 'leadId' => $this->leadId->value, 'transition' => $this->transition, 'previousState' => $this->previousState->value, 'currentState' => $this->currentState->value, 'action' => $this->action->value, 'version' => $this->version, 'occurredVersion' => $this->occurredVersion];
    }
}
