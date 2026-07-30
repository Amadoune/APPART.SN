<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;

final readonly class LeadLifecycleEvent
{
    public function __construct(public LeadLifecycleEventMetadata $metadata, public LeadLifecycleEventPayload $payload)
    {
        $transition = new LeadLifecycleTransition($payload->previousState, $payload->currentState, $payload->action);
        $certifiedType = (new LeadLifecycleEventCatalog)->typeFor($transition);
        $derivedId = LeadLifecycleEventId::derive(
            $metadata->eventType,
            $metadata->payloadVersion,
            $payload->leadId,
            $transition,
            $payload->occurredVersion,
        );

        if (
            $certifiedType !== $metadata->eventType
            || $metadata->payloadVersion->value !== $payload->version
            || $derivedId->value !== $payload->eventId->value
        ) {
            throw new \InvalidArgumentException('Inconsistent Lead event envelope.');
        }
    }

    /**
     * @return array{
     *     eventId: string,
     *     eventType: string,
     *     payloadVersion: int,
     *     payload: array<string, int|string>,
     *     metadata: array<string, int|string>
     * }
     */
    public function canonical(): array
    {
        return ['eventId' => $this->payload->eventId->value, 'eventType' => $this->metadata->eventType->value, 'payloadVersion' => $this->metadata->payloadVersion->value, 'payload' => $this->payload->fields(), 'metadata' => $this->metadata->fields()];
    }
}
