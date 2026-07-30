<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

final readonly class LeadLifecycleTransition
{
    public function __construct(
        public LeadLifecycleState $from,
        public LeadLifecycleState $to,
        public LeadLifecycleAction $action,
    ) {}
}
