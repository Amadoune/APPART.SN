<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\Contract\AntiAbuseOwnerSource;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicAntiAbuseOwnerSourceRuntimeAvailabilityPolicy implements AntiAbuseOwnerSourceRuntimeAvailabilityPolicy
{
    private const PROBE_INTENT_ID = '00000000-0000-4000-8000-000000005408';

    public function __construct(private AntiAbuseOwnerSource $source) {}

    public function inspect(): AntiAbuseOwnerSourceRuntimeAvailability
    {
        try {
            $result = $this->source->at(
                LeadIngressIntentId::fromString(self::PROBE_INTENT_ID),
                new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                AntiAbuseRevisionReadStatus::Found,
                AntiAbuseRevisionReadStatus::Missing => AntiAbuseOwnerSourceRuntimeAvailability::Available,
                AntiAbuseRevisionReadStatus::Corrupted => AntiAbuseOwnerSourceRuntimeAvailability::Corrupted,
                AntiAbuseRevisionReadStatus::DependencyUnavailable => AntiAbuseOwnerSourceRuntimeAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return AntiAbuseOwnerSourceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
