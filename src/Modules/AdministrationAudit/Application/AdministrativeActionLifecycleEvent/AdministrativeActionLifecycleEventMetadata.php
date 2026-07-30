<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEventMetadata
{
    public function __construct(
        public AdministrativeActionLifecycleEventType $eventType,
        public AdministrativeActionLifecycleEventPayloadVersion $payloadVersion,
        public ActorId $actor,
        public AdministrativeActionHistoricalMirrorOccurredAt $occurredAt,
        public AdministrativeActionHistoricalMirrorOccurredAt $recordedAt,
    ) {
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Administrative Action Lifecycle recorded time precedes occurrence.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return [
            'eventType' => $this->eventType->value,
            'payloadVersion' => $this->payloadVersion->value,
            'actorId' => $this->actor->value,
            'occurredAt' => $this->occurredAt->canonical(),
            'recordedAt' => $this->recordedAt->canonical(),
        ];
    }
}
