<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerReader;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Contract\LeadContactConsentReaderV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentResultV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentStatusV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

final readonly class OwnerLeadContactConsentReaderV1 implements LeadContactConsentReaderV1
{
    public function __construct(private ConsentOwnerSourceRuntimeReadV1 $runtimeRead) {}

    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): LeadContactConsentResultV1 {
        $status = match ($this->runtimeRead->read($intentId, $observedAt)->status) {
            ConsentOwnerSourceRuntimeReadStatus::Granted => LeadContactConsentStatusV1::Granted,
            ConsentOwnerSourceRuntimeReadStatus::Denied => LeadContactConsentStatusV1::Denied,
            ConsentOwnerSourceRuntimeReadStatus::Missing => LeadContactConsentStatusV1::Missing,
            ConsentOwnerSourceRuntimeReadStatus::Corrupted => LeadContactConsentStatusV1::Corrupted,
            ConsentOwnerSourceRuntimeReadStatus::DependencyUnavailable => LeadContactConsentStatusV1::DependencyUnavailable,
        };

        return new LeadContactConsentResultV1($status);
    }
}
