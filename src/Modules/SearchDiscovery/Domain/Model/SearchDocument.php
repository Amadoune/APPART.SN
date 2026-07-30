<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ProjectionState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use DateTimeImmutable;

final readonly class SearchDocument
{
    private function __construct(
        public SearchDocumentId $id,
        public ListingId $listingId,
        public SearchProjection $projection,
        public DateTimeImmutable $indexedAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    public static function index(SearchDocumentId $id, ListingId $listingId, SearchProjection $projection, DateTimeImmutable $at): self
    {
        return new self($id, $listingId, $projection, $at, $at);
    }

    public function project(SearchProjection $projection, DateTimeImmutable $at): self
    {
        return new self($this->id, $this->listingId, $projection, $this->indexedAt, $at);
    }

    public function state(): ProjectionState
    {
        return $this->projection->state;
    }
}
