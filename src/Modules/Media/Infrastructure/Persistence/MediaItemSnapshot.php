<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

final readonly class MediaItemSnapshot
{
    public function __construct(
        public string $id,
        public string $collectionId,
        public string $type,
        public string $checksum,
        public int $order,
        public ?string $caption,
        public string $source,
        public string $status,
        public bool $primary,
        public string $addedAt,
        public ?string $removedAt,
        public ?string $archivedAt,
    ) {}
}
