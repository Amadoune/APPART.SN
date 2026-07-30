<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

final readonly class ListingSnapshot
{
    /** @param list<ListingRevisionSnapshot> $revisions */
    public function __construct(
        public string $id,
        public string $propertyId,
        public string $status,
        public string $lastChangedAt,
        public ?string $expirationDate,
        public int $version,
        public array $revisions,
    ) {}
}
