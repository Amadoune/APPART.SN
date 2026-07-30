<?php

namespace Tests\Unit\Modules\ContactsLeads\Support;

use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final class FakeListingCatalog implements ListingCatalog
{
    /** @var array<string, ListingContactEvidence> */
    private array $items = [];

    public function set(ListingId $id, ListingContactability $state, ?EligibilityRevision $revision = null): void
    {
        $this->items[$id->value] = new ListingContactEvidence($state, $revision ?? new EligibilityRevision('90000000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new \DateTimeImmutable('2026-07-17T10:00:00+00:00'))));
    }

    public function contactabilityOf(ListingId $listing): ListingContactEvidence
    {
        return $this->items[$listing->value] ?? new ListingContactEvidence(ListingContactability::Missing, new EligibilityRevision('90000000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new \DateTimeImmutable('2026-07-17T10:00:00+00:00'))));
    }
}
