<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use InvalidArgumentException;

final readonly class LeadLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        LeadLifecycleEventType $type,
        LeadLifecycleEventPayloadVersion $payloadVersion,
        LeadId $id,
        LeadLifecycleTransition $transition,
        int $occurredVersion,
    ): self {
        if ($occurredVersion < 2) {
            throw new InvalidArgumentException('Event version must be at least two.');
        }

        return new self(hash('sha256', implode("\n", [
            $type->value,
            (string) $payloadVersion->value,
            $id->value,
            $transition->from->value,
            $transition->action->value,
            $transition->to->value,
            (string) $occurredVersion,
        ])));
    }
}
