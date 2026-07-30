<?php

namespace Tests\Unit\Modules\SearchDiscovery\Support;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchIndexRegistry;
use Appart\Modules\SearchDiscovery\Domain\Exception\ConcurrentSearchIndexModification;
use Appart\Modules\SearchDiscovery\Domain\Exception\ListingDocumentConflict;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchDocumentIdentityConflict;
use Appart\Modules\SearchDiscovery\Domain\Exception\SearchIndexIdConflict;
use Appart\Modules\SearchDiscovery\Domain\Model\SearchIndex;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;

final class FakeSearchIndexRegistry implements SearchIndexRegistry
{
    /** @var array<string, SearchIndex> */
    private array $indexes = [];

    /** @var array<string, string> */
    private array $listings = [];

    /** @var array<string, string> */
    private array $documents = [];

    private bool $failNextWrite = false;

    public function find(SearchIndexId $id): ?SearchIndex
    {
        return isset($this->indexes[$id->value]) ? clone $this->indexes[$id->value] : null;
    }

    public function findByListing(ListingId $listingId): ?SearchIndex
    {
        $indexId = $this->listings[$listingId->value] ?? null;

        return $indexId === null ? null : clone $this->indexes[$indexId];
    }

    public function add(SearchIndex $index): void
    {
        $this->guardFailure();
        if (isset($this->indexes[$index->id()->value])) {
            throw new SearchIndexIdConflict;
        }
        if (isset($this->listings[$index->listingId()->value])) {
            throw new ListingDocumentConflict;
        }
        if (isset($this->documents[$index->document()->id->value])) {
            throw new SearchDocumentIdentityConflict;
        }

        $this->indexes[$index->id()->value] = $this->clean($index);
        $this->listings[$index->listingId()->value] = $index->id()->value;
        $this->documents[$index->document()->id->value] = $index->id()->value;
    }

    public function save(SearchIndex $index, int $expectedVersion): void
    {
        $this->guardFailure();
        $stored = $this->indexes[$index->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentSearchIndexModification;
        }

        $this->indexes[$index->id()->value] = $this->clean($index);
    }

    public function failNextWrite(): void
    {
        $this->failNextWrite = true;
    }

    private function guardFailure(): void
    {
        if ($this->failNextWrite) {
            $this->failNextWrite = false;
            throw new ConcurrentSearchIndexModification;
        }
    }

    private function clean(SearchIndex $index): SearchIndex
    {
        $snapshot = clone $index;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
