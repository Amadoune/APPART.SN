<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

final readonly class AdvertiserEligibilityEvidence
{
    public function __construct(public AdvertiserEligibility $state, public EligibilityRevision $revision) {}
}
