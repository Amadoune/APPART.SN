<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract\ConsentOwnerSourceRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Throwable;

final readonly class DeterministicConsentOwnerSourceRuntimeReadV1 implements ConsentOwnerSourceRuntimeReadV1
{
    private const VERSION = 'consent-owner-source-runtime-read-v1';

    private const COMPONENT = 'contacts-leads.consent-owner-source-runtime-read';

    public function __construct(
        private ConsentOwnerSource $source,
        private ConsentOwnerSourceRuntimeReadPolicy $policy,
    ) {}

    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): ConsentOwnerSourceRuntimeReadResult {
        try {
            return $this->policy->reduce($this->source->at($intentId, $observedAt->value));
        } catch (Throwable) {
            return ConsentOwnerSourceRuntimeReadResult::dependencyUnavailable();
        }
    }

    public function diagnostics(): ConsentOwnerSourceRuntimeReadDiagnostics
    {
        return new ConsentOwnerSourceRuntimeReadDiagnostics(self::VERSION, self::COMPONENT);
    }
}
