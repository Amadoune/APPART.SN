<?php

namespace App\Application\PublicGeographyRevision;

use InvalidArgumentException;

final readonly class PublicGeographyRevisionChecksum
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('A public geography revision checksum must be a SHA-256 hexadecimal value.');
        }

        return new self($value);
    }

    public static function fromCanonicalPayload(string $payload): self
    {
        return new self(hash('sha256', $payload));
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
