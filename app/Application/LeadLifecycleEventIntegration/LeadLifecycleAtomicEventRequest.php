<?php

namespace App\Application\LeadLifecycleEventIntegration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use InvalidArgumentException;

final readonly class LeadLifecycleAtomicEventRequest
{
    public function __construct(
        public LeadId $leadId,
        public LeadLifecycleAction $action,
        public int $expectedVersion,
        public LeadLifecycleActorId $actor,
        public LeadLifecycleOccurredAt $occurredAt,
        public LeadLifecycleOccurredAt $recordedAt,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected Lead lifecycle version must be positive.');
        }
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Recorded time precedes occurrence.');
        }
    }

    public function transitionRequest(): LeadLifecycleTransitionRequest
    {
        return new LeadLifecycleTransitionRequest(
            $this->leadId,
            $this->action,
            $this->expectedVersion,
            new LeadLifecycleTransitionContext($this->actor, $this->occurredAt),
        );
    }
}
