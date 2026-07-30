<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use InvalidArgumentException;

final readonly class MediaItemLifecycleActorId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('The media item lifecycle actor id must be a UUID.');
        }

        return new self($value);
    }
}
