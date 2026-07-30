<?php

namespace Tests\Unit\Modules\ModerationReports\Support;

use Appart\Modules\ModerationReports\Application\Contract\ListingCatalog;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingEligibility;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;

final class FakeListingCatalog implements ListingCatalog
{
    /** @var array<string,ListingEligibility> */
    private array $items = [];

    public function set(ListingId $id, ListingEligibility $eligibility): void
    {
        $this->items[$id->value] = $eligibility;
    }

    public function eligibilityOf(ListingId $id): ListingEligibility
    {
        return $this->items[$id->value] ?? ListingEligibility::Missing;
    }
}
