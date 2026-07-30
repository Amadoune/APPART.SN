<?php

namespace App\Application\PublicMediaRevision;

use InvalidArgumentException;

final readonly class PublicMediaRevision
{
    public function __construct(
        public PublicMediaRevisionVersion $version,
        public PublicMediaRevisionChecksum $checksum,
        public string $causationKey,
    ) {
        if (trim($causationKey) === '') {
            throw new InvalidArgumentException('A public media revision requires an explicit causation key.');
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
