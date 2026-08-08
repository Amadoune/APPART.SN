<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadPolicy;

final readonly class DeterministicLeadIngressAntiAbuseRuntimeReadPolicy implements LeadIngressAntiAbuseRuntimeReadPolicy
{
    public function reduce(AntiAbuseRevisionReadResult $sourceResult): LeadIngressAntiAbuseRuntimeReadResult
    {
        return match ($sourceResult->status) {
            AntiAbuseRevisionReadStatus::Found => $this->reduceFound($sourceResult),
            AntiAbuseRevisionReadStatus::Missing => LeadIngressAntiAbuseRuntimeReadResult::missing(),
            AntiAbuseRevisionReadStatus::Corrupted => LeadIngressAntiAbuseRuntimeReadResult::corrupted(),
            AntiAbuseRevisionReadStatus::DependencyUnavailable => LeadIngressAntiAbuseRuntimeReadResult::dependencyUnavailable(),
        };
    }

    private function reduceFound(AntiAbuseRevisionReadResult $sourceResult): LeadIngressAntiAbuseRuntimeReadResult
    {
        if ($sourceResult->revision === null) {
            return LeadIngressAntiAbuseRuntimeReadResult::corrupted();
        }

        return match ($sourceResult->revision->decision) {
            AntiAbuseRevisionDecision::Allowed => LeadIngressAntiAbuseRuntimeReadResult::allowed(),
            AntiAbuseRevisionDecision::Blocked => LeadIngressAntiAbuseRuntimeReadResult::blocked(),
        };
    }
}
