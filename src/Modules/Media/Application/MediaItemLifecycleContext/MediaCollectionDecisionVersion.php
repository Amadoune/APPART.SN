<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use InvalidArgumentException;

final readonly class MediaCollectionDecisionVersion
{
    public function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The media collection decision version cannot be negative.');
        }
    }
}
