<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use InvalidArgumentException;

final readonly class LeadLifecycleContextualAppend
{
    public function __construct(
        public LeadId $leadId,
        public LeadLifecycleTransition $transition,
        public int $expectedVersion,
        public LeadLifecycleTransitionContext $context,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected lead lifecycle version must be positive.');
        }
    }

    public function nextVersion(): int
    {
        return $this->expectedVersion + 1;
    }
}
