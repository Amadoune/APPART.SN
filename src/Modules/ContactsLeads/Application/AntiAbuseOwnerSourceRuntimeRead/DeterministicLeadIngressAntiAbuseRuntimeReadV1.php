<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract\LeadIngressAntiAbuseRuntimeReadV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Throwable;

final readonly class DeterministicLeadIngressAntiAbuseRuntimeReadV1 implements LeadIngressAntiAbuseRuntimeReadV1
{
    private const DIAGNOSTIC_PROBE_INTENT_ID = '00000000-0000-4000-8000-000000005412';

    private const RUNTIME_READ_ID = 'contacts-leads.anti-abuse-owner-source-runtime-read';

    private const VERSION = 'anti-abuse-owner-source-runtime-read-v1';

    public function __construct(
        private AntiAbuseOwnerSource $source,
        private LeadIngressAntiAbuseRuntimeReadPolicy $policy,
    ) {}

    public function read(
        LeadIngressIntentId $intentId,
        LeadIngressAntiAbuseObservedAt $observedAt,
    ): LeadIngressAntiAbuseRuntimeReadResult {
        try {
            return $this->policy->reduce($this->source->at($intentId, $observedAt));
        } catch (Throwable) {
            return LeadIngressAntiAbuseRuntimeReadResult::dependencyUnavailable();
        }
    }

    public function diagnostics(): LeadIngressAntiAbuseRuntimeReadDiagnostics
    {
        return new LeadIngressAntiAbuseRuntimeReadDiagnostics(
            self::RUNTIME_READ_ID,
            self::VERSION,
            $this->availability(),
        );
    }

    private function availability(): LeadIngressAntiAbuseRuntimeReadAvailability
    {
        try {
            $result = $this->source->at(
                LeadIngressIntentId::fromString(self::DIAGNOSTIC_PROBE_INTENT_ID),
                new LeadIngressAntiAbuseObservedAt(new \DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                AntiAbuseRevisionReadStatus::Found,
                AntiAbuseRevisionReadStatus::Missing => LeadIngressAntiAbuseRuntimeReadAvailability::Available,
                AntiAbuseRevisionReadStatus::Corrupted => LeadIngressAntiAbuseRuntimeReadAvailability::Corrupted,
                AntiAbuseRevisionReadStatus::DependencyUnavailable => LeadIngressAntiAbuseRuntimeReadAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return LeadIngressAntiAbuseRuntimeReadAvailability::DependencyUnavailable;
        }
    }
}
