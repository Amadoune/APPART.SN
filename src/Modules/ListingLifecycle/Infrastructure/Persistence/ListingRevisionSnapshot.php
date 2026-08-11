<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

final readonly class ListingRevisionSnapshot
{
    public function __construct(
        public int $sequence,
        public string $id,
        public ?string $previousStatus,
        public string $status,
        public string $actorId,
        public string $trigger,
        public ?string $reason,
        public string $origin,
        public string $occurredAt,
    ) {}
}
