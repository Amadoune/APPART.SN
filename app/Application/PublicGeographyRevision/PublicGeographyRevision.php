<?php

namespace App\Application\PublicGeographyRevision;

use InvalidArgumentException;

final readonly class PublicGeographyRevision
{
    public function __construct(
        public PublicGeographyRevisionVersion $version,
        public PublicGeographyRevisionChecksum $checksum,
        public string $causationKey,
    ) {
        if (trim($causationKey) === '') {
            throw new InvalidArgumentException('A public geography revision requires an explicit causation key.');
        }
    }

    public function watermarkVersion(): int
    {
        return $this->version->value;
    }

    public function sameFactAs(self $other): bool
    {
        return $this->version->value === $other->version->value
            && $this->checksum->equals($other->checksum)
            && $this->causationKey === $other->causationKey;
    }
}
