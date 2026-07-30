<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use DomainException;

final readonly class LeadLifecycleEventCatalog
{
    public function typeFor(LeadLifecycleTransition $transition): LeadLifecycleEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [LeadLifecycleState::Created, LeadLifecycleAction::Deliver, LeadLifecycleState::Delivered] => LeadLifecycleEventType::Delivered,[LeadLifecycleState::Created, LeadLifecycleAction::Reject, LeadLifecycleState::Rejected] => LeadLifecycleEventType::Rejected,[LeadLifecycleState::Delivered, LeadLifecycleAction::Close, LeadLifecycleState::Closed],[LeadLifecycleState::Rejected, LeadLifecycleAction::Close, LeadLifecycleState::Closed] => LeadLifecycleEventType::Closed,default => throw new DomainException('Transition is not certified for an event.')
        };
    }
}
