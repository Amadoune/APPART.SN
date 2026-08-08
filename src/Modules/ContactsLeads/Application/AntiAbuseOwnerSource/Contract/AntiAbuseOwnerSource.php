<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

interface AntiAbuseOwnerSource
{
    public function append(AntiAbuseRevisionState $revision): AntiAbuseRevisionWriteResult;

    public function at(
        LeadIngressIntentId $intentId,
        LeadIngressAntiAbuseObservedAt $observedAt,
    ): AntiAbuseRevisionReadResult;

    /** @return list<AntiAbuseRevisionState> */
    public function history(LeadIngressIntentId $intentId): array;
}
