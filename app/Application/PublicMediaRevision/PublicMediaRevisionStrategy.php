<?php

namespace App\Application\PublicMediaRevision;

use InvalidArgumentException;

final readonly class PublicMediaRevisionStrategy
{
    public function revise(int $sourceSequence, string $canonicalPublicPayload, string $causationKey): PublicMediaRevision
    {
        if ($canonicalPublicPayload === '') {
            throw new InvalidArgumentException('A public media revision requires a canonical public payload.');
        }

        return new PublicMediaRevision(
            PublicMediaRevisionVersion::fromInt($sourceSequence),
            PublicMediaRevisionChecksum::fromCanonicalPayload($canonicalPublicPayload),
            $causationKey,
        );
    }
}
