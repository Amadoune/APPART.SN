<?php

namespace Tests\Unit\Modules\ListingLifecycle\Support;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Event\ListingEvent;
use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

final class FakeListingRegistry implements ListingRegistry
{
    public int $saveCalls = 0;

    /** @var list<ListingEvent> */
    public array $lastSavedEvents = [];

    /** @var array<string, Listing> */
    private array $listings = [];

    private bool $failNextSave = false;

    public function find(ListingId $id): ?Listing
    {
        return isset($this->listings[$id->value]) ? clone $this->listings[$id->value] : null;
    }

    public function add(Listing $listing): void
    {
        if (isset($this->listings[$listing->id()->value])) {
            throw new ListingIdConflict;
        }
        $this->listings[$listing->id()->value] = $this->cleanSnapshot($listing);
    }

    public function save(Listing $listing, int $expectedVersion): void
    {
        $this->saveCalls++;
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentListingModification;
        }
        $stored = $this->listings[$listing->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentListingModification;
        }
        $eventSnapshot = clone $listing;
        $this->lastSavedEvents = $eventSnapshot->releaseEvents();
        $this->listings[$listing->id()->value] = $this->cleanSnapshot($listing);
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }

    private function cleanSnapshot(Listing $listing): Listing
    {
        $snapshot = clone $listing;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
