<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use DomainException;

final readonly class LeadLifecycleTransitionEligibilityPolicy
{
    public function requirementFor(LeadLifecycleAction $action): LeadLifecycleEligibilityRequirement
    {
        return match ($action) {
            LeadLifecycleAction::Deliver,
            LeadLifecycleAction::Reject,
            LeadLifecycleAction::Close => LeadLifecycleEligibilityRequirement::NotRequired,
            LeadLifecycleAction::Unknown => throw new DomainException('Unknown is not an application transition action.'),
        };
    }
}
