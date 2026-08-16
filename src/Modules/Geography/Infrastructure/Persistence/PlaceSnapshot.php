<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

final readonly class PlaceSnapshot
{
    /** @param list<PlaceAliasSnapshot> $aliases */
    public function __construct(
        public string $id,
        public string $officialName,
        public string $code,
        public string $type,
        public string $countryCode,
        public ?string $parentId,
        public ?string $parentType,
        public ?float $latitude,
        public ?float $longitude,
        public array $aliases,
        public bool $enabled,
        public ?string $mergedInto,
        public int $version,
    ) {}
}
