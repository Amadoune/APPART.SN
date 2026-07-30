<?php

namespace Tests\Unit\Modules\ContactsLeads\Support;

use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibilityEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final class FakeAdvertiserCatalog implements AdvertiserCatalog
{
    /** @var array<string, AdvertiserEligibilityEvidence> */
    private array $items = [];

    public function set(AdvertiserId $advertiser, ListingId $listing, AdvertiserEligibility $state, ?EligibilityRevision $revision = null): void
    {
        $this->items[$advertiser->value.'|'.$listing->value] = new AdvertiserEligibilityEvidence($state, $revision ?? new EligibilityRevision('90000000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new \DateTimeImmutable('2026-07-17T10:00:00+00:00'))));
    }

    public function eligibilityFor(AdvertiserId $advertiser, ListingId $listing): AdvertiserEligibilityEvidence
    {
        return $this->items[$advertiser->value.'|'.$listing->value] ?? new AdvertiserEligibilityEvidence(AdvertiserEligibility::Missing, new EligibilityRevision('90000000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new \DateTimeImmutable('2026-07-17T10:00:00+00:00'))));
    }
}
