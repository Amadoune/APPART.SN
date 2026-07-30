<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use InvalidArgumentException;

final readonly class ProfessionalStatusEvent
{
    public function __construct(public ProfessionalStatusEventMetadata $metadata, public ProfessionalStatusEventPayload $payload)
    {
        $transition = new ProfessionalStatusTransition($payload->previousState, $payload->currentState, $payload->action);
        $type = (new ProfessionalStatusEventCatalog)->typeFor($transition);
        $eventId = ProfessionalStatusEventId::derive($metadata->eventType, $metadata->payloadVersion, $payload->professionalId, $transition, $payload->occurredVersion);
        if ($type !== $metadata->eventType || $metadata->payloadVersion->value !== $payload->version || $eventId->value !== $payload->eventId->value) {
            throw new InvalidArgumentException('Inconsistent professional status event envelope.');
        }
    }

    /** @return array{eventId:string,eventType:string,payloadVersion:int,payload:array<string,int|string>,metadata:array<string,int|string>} */
    public function canonical(): array
    {
        return ['eventId' => $this->payload->eventId->value, 'eventType' => $this->metadata->eventType->value, 'payloadVersion' => $this->metadata->payloadVersion->value, 'payload' => $this->payload->fields(), 'metadata' => $this->metadata->fields()];
    }
}
