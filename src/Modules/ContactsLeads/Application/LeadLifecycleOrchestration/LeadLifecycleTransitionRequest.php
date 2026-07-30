<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use InvalidArgumentException;

final readonly class LeadLifecycleTransitionRequest
{
    public function __construct(public LeadId $leadId, public LeadLifecycleAction $action, public int $expectedVersion, public LeadLifecycleTransitionContext $context)
    {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('Expected version must be positive.');
        } if ($action === LeadLifecycleAction::Unknown) {
            throw new InvalidArgumentException('Unknown is not an application action.');
        }
    }
}
