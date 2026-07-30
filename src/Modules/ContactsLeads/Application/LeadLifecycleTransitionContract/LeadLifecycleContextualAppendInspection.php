<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use InvalidArgumentException;

final readonly class LeadLifecycleContextualAppendInspection
{
    public function __construct(
        public LeadId $leadId,
        public int $version,
        public LeadLifecycleTransition $transition,
        public LeadLifecycleTransitionContext $context,
        public LeadLifecycleContextChecksum $checksum,
    ) {
        if ($version < 2) {
            throw new InvalidArgumentException('A contextual append version must be at least two.');
        }
    }
}
