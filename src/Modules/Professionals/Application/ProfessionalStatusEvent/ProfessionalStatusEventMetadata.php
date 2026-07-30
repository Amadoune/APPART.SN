<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use InvalidArgumentException;

final readonly class ProfessionalStatusEventMetadata
{
    public function __construct(
        public ProfessionalStatusEventType $eventType,
        public ProfessionalStatusEventPayloadVersion $payloadVersion,
        public ProfessionalStatusActorId $actor,
        public ProfessionalStatusOccurredAt $occurredAt,
        public ProfessionalStatusOccurredAt $recordedAt,
    ) {
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Professional status recorded time precedes occurrence.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return ['eventType' => $this->eventType->value, 'payloadVersion' => $this->payloadVersion->value, 'actorId' => $this->actor->value, 'occurredAt' => $this->occurredAt->canonical(), 'recordedAt' => $this->recordedAt->canonical()];
    }
}
