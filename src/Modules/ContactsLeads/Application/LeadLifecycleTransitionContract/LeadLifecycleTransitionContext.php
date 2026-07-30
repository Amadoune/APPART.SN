<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;

final readonly class LeadLifecycleTransitionContext
{
    public function __construct(
        public LeadLifecycleActorId $actor,
        public LeadLifecycleOccurredAt $occurredAt,
    ) {}
}
