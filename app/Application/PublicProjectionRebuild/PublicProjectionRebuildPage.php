<?php

namespace App\Application\PublicProjectionRebuild;

use InvalidArgumentException;

final readonly class PublicProjectionRebuildPage
{
    /** @param list<string> $listingIds */
    public function __construct(public array $listingIds, public ?string $nextCheckpoint)
    {
        if ($listingIds === [] && $nextCheckpoint !== null) {
            throw new InvalidArgumentException('An empty rebuild page cannot advance the checkpoint.');
        }
    }
}
