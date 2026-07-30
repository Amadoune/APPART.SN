<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

final readonly class MediaCollectionSnapshot
{
    /** @param list<MediaItemSnapshot> $items */
    public function __construct(
        public string $id,
        public string $propertyId,
        public string $lastChangedAt,
        public int $version,
        public array $items,
    ) {}
}
