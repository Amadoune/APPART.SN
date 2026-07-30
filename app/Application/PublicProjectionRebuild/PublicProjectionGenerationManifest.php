<?php

namespace App\Application\PublicProjectionRebuild;

use InvalidArgumentException;

final readonly class PublicProjectionGenerationManifest
{
    /** @var array<string, PublicProjectionGenerationManifestEntry> */
    public array $entries;

    /** @param list<PublicProjectionGenerationManifestEntry> $entries */
    public function __construct(array $entries)
    {
        if ($entries === []) {
            throw new InvalidArgumentException('A generation manifest cannot be empty.');
        }
        $indexed = [];
        foreach ($entries as $entry) {
            if (isset($indexed[$entry->listingId])) {
                throw new InvalidArgumentException('A generation manifest cannot contain duplicate listings.');
            }
            $indexed[$entry->listingId] = $entry;
        }
        $this->entries = $indexed;
    }

    public function count(): int
    {
        return count($this->entries);
    }
}
