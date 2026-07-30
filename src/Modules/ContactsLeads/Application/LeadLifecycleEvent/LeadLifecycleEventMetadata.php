<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;

final readonly class LeadLifecycleEventMetadata
{
    public function __construct(public LeadLifecycleEventType $eventType, public LeadLifecycleEventPayloadVersion $payloadVersion, public LeadLifecycleActorId $actor, public LeadLifecycleOccurredAt $occurredAt, public LeadLifecycleOccurredAt $recordedAt)
    {
        if ($recordedAt->value < $occurredAt->value) {
            throw new \InvalidArgumentException('Recorded time precedes occurrence.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return ['eventType' => $this->eventType->value, 'payloadVersion' => $this->payloadVersion->value, 'actorId' => $this->actor->value, 'occurredAt' => $this->occurredAt->value->format('Y-m-d\TH:i:s.uP'), 'recordedAt' => $this->recordedAt->value->format('Y-m-d\TH:i:s.uP')];
    }
}
