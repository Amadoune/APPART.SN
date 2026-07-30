<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use InvalidArgumentException;

final readonly class MediaItemLifecycleExpectedVersion
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('The expected media item lifecycle version must be positive.');
        }
    }
}
