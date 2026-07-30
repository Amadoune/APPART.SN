<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

final readonly class ListingContactEvidence
{
    public function __construct(public ListingContactability $state, public EligibilityRevision $revision) {}
}
