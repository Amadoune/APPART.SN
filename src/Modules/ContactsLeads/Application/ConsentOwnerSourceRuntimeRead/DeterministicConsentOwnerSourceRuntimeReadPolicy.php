<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadPolicy;

final readonly class DeterministicConsentOwnerSourceRuntimeReadPolicy implements ConsentOwnerSourceRuntimeReadPolicy
{
    public function reduce(ConsentRevisionReadResult $sourceResult): ConsentOwnerSourceRuntimeReadResult
    {
        return match ($sourceResult->status) {
            ConsentRevisionReadStatus::Found => $this->reduceFound($sourceResult),
            ConsentRevisionReadStatus::Missing => ConsentOwnerSourceRuntimeReadResult::missing(),
            ConsentRevisionReadStatus::Corrupted => ConsentOwnerSourceRuntimeReadResult::corrupted(),
            ConsentRevisionReadStatus::DependencyUnavailable => ConsentOwnerSourceRuntimeReadResult::dependencyUnavailable(),
        };
    }

    private function reduceFound(ConsentRevisionReadResult $sourceResult): ConsentOwnerSourceRuntimeReadResult
    {
        if ($sourceResult->revision === null) {
            return ConsentOwnerSourceRuntimeReadResult::corrupted();
        }

        return match ($sourceResult->revision->decision) {
            ConsentRevisionDecision::Granted => ConsentOwnerSourceRuntimeReadResult::granted(),
            ConsentRevisionDecision::Denied,
            ConsentRevisionDecision::Undecided,
            ConsentRevisionDecision::Withdrawn => ConsentOwnerSourceRuntimeReadResult::denied(),
        };
    }
}
