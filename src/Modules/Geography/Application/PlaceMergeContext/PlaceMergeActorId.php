<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use InvalidArgumentException;

final readonly class PlaceMergeActorId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('The place merge actor id must be a UUID.');
        }

        return new self($value);
    }
}
