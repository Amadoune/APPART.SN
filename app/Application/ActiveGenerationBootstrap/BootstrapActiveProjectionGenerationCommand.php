<?php

namespace App\Application\ActiveGenerationBootstrap;

use InvalidArgumentException;

final readonly class BootstrapActiveProjectionGenerationCommand
{
    /** @param list<string> $listingIds */
    public function __construct(public string $generationId, public array $listingIds, public string $operatorId)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $generationId) !== 1
            || $listingIds === []
            || trim($operatorId) === '') {
            throw new InvalidArgumentException('Invalid initial projection bootstrap command.');
        }
        foreach ($listingIds as $listingId) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $listingId) !== 1) {
                throw new InvalidArgumentException('Invalid Listing scope identity.');
            }
        }
        if (count(array_unique($listingIds)) !== count($listingIds)) {
            throw new InvalidArgumentException('Duplicate Listing scope identity.');
        }
    }

    public function scopeChecksum(): string
    {
        $ids = $this->listingIds;
        sort($ids, SORT_STRING);

        return hash('sha256', json_encode($ids, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
