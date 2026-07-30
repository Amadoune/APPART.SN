<?php

namespace App\Application\PublicGeographyRevision;

use InvalidArgumentException;

final readonly class PublicGeographyRevisionStrategy
{
    public function revise(int $sourceSequence, string $canonicalPublicPayload, string $causationKey): PublicGeographyRevision
    {
        if ($canonicalPublicPayload === '') {
            throw new InvalidArgumentException('A public geography revision requires a canonical public payload.');
        }

        return new PublicGeographyRevision(
            PublicGeographyRevisionVersion::fromInt($sourceSequence),
            PublicGeographyRevisionChecksum::fromCanonicalPayload($canonicalPublicPayload),
            $causationKey,
        );
    }
}
