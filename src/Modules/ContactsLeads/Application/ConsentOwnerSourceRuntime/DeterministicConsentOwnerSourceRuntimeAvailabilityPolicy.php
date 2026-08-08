<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\Contract\ConsentOwnerSource;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use DateTimeImmutable;

final readonly class DeterministicConsentOwnerSourceRuntimeAvailabilityPolicy implements ConsentOwnerSourceRuntimeAvailabilityPolicy
{
    private const PROBE_INTENT_ID = '00000000-0000-4000-8000-000000005400';

    public function __construct(private ConsentOwnerSource $source) {}

    public function inspect(): ConsentOwnerSourceRuntimeAvailability
    {
        $result = $this->source->at(
            LeadIngressIntentId::fromString(self::PROBE_INTENT_ID),
            new DateTimeImmutable('9999-12-31T23:59:59.999999Z'),
        );

        return match ($result->status) {
            ConsentRevisionReadStatus::Found,
            ConsentRevisionReadStatus::Missing => ConsentOwnerSourceRuntimeAvailability::Available,
            ConsentRevisionReadStatus::Corrupted => ConsentOwnerSourceRuntimeAvailability::Corrupted,
            ConsentRevisionReadStatus::DependencyUnavailable => ConsentOwnerSourceRuntimeAvailability::DependencyUnavailable,
        };
    }
}
