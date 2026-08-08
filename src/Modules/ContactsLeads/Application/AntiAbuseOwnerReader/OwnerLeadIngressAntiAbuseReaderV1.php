<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerReader;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Contract\LeadIngressAntiAbuseReaderV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseStatusV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

final readonly class OwnerLeadIngressAntiAbuseReaderV1 implements LeadIngressAntiAbuseReaderV1
{
    public function __construct(private LeadIngressAntiAbuseRuntimeReadV1 $runtimeRead) {}

    public function read(
        LeadIngressIntentId $intentId,
        LeadIngressAntiAbuseObservedAt $observedAt,
    ): LeadIngressAntiAbuseResultV1 {
        $status = match ($this->runtimeRead->read($intentId, $observedAt)->status) {
            LeadIngressAntiAbuseRuntimeReadStatus::Allowed => LeadIngressAntiAbuseStatusV1::Allowed,
            LeadIngressAntiAbuseRuntimeReadStatus::Blocked => LeadIngressAntiAbuseStatusV1::Blocked,
            LeadIngressAntiAbuseRuntimeReadStatus::Missing => LeadIngressAntiAbuseStatusV1::Missing,
            LeadIngressAntiAbuseRuntimeReadStatus::Corrupted => LeadIngressAntiAbuseStatusV1::Corrupted,
            LeadIngressAntiAbuseRuntimeReadStatus::DependencyUnavailable => LeadIngressAntiAbuseStatusV1::DependencyUnavailable,
        };

        return new LeadIngressAntiAbuseResultV1($status);
    }
}
