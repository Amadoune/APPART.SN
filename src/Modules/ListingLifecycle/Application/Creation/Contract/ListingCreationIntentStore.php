<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation\Contract;

use Appart\Modules\ListingLifecycle\Application\Creation\ListingCreationIntent;

interface ListingCreationIntentStore
{
    public function reserve(ListingCreationIntent $intent): bool;

    public function find(string $intentId): ?ListingCreationIntent;

    public function markApplied(string $intentId, int $aggregateVersion): void;
}
