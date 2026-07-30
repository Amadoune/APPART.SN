<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

final readonly class PropertySnapshot
{
    public function __construct(
        public string $id,
        public string $reference,
        public string $type,
        public ?int $surface,
        public int $rooms,
        public int $bathrooms,
        public ?int $constructionYear,
        public ?AddressSnapshot $address,
        public string $status,
        public string $lastChangedAt,
        public int $version,
    ) {}
}
