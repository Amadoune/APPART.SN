<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEvent
{
    public function __construct(
        public AdministrativeActionLifecycleEventMetadata $metadata,
        public AdministrativeActionLifecycleEventPayload $payload,
    ) {
        $transition = new AdministrativeActionLifecycleTransition(
            $payload->previousState,
            $payload->currentState,
            $payload->action,
        );
        $type = (new AdministrativeActionLifecycleEventCatalog)->typeFor($transition);
        $eventId = AdministrativeActionLifecycleEventId::derive(
            $metadata->eventType,
            $metadata->payloadVersion,
            $payload->actionId,
            $transition,
            $payload->occurredVersion,
        );
        if ($type !== $metadata->eventType
            || $metadata->payloadVersion->value !== $payload->version
            || $eventId->value !== $payload->eventId->value) {
            throw new InvalidArgumentException('Inconsistent Administrative Action Lifecycle event.');
        }
    }

    /** @return array<string, mixed> */
    public function canonicalWithoutChecksum(): array
    {
        return [
            'eventId' => $this->payload->eventId->value,
            'eventType' => $this->metadata->eventType->value,
            'payloadVersion' => $this->metadata->payloadVersion->value,
            'payload' => $this->payload->fields(),
            'metadata' => $this->metadata->fields(),
        ];
    }

    public function checksum(): AdministrativeActionLifecycleEventChecksum
    {
        return AdministrativeActionLifecycleEventChecksum::derive($this->canonicalWithoutChecksum());
    }

    /** @return array<string, mixed> */
    public function canonical(): array
    {
        return [...$this->canonicalWithoutChecksum(), 'checksum' => $this->checksum()->value];
    }
}
