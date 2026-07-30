<?php

namespace Appart\Modules\Media\Application\IngestionPersistence;

abstract readonly class AbstractIngestionState
{
    /** @param array<string, bool|int|string|null> $payload */
    public function __construct(
        public string $id,
        public string $state,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public array $payload,
    ) {}
}
