<?php

namespace App\Application\PublicProjectionRetry;

use InvalidArgumentException;

final readonly class PublicProjectionReplayAuthorization
{
    private function __construct(public string $reference) {}

    public static function fromReference(string $reference): self
    {
        if ($reference === '' || trim($reference) !== $reference) {
            throw new InvalidArgumentException('Replay authorization reference is required.');
        }

        return new self($reference);
    }
}
