<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use InvalidArgumentException;

final readonly class PlaceMergeObservedTargetVersion
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('The observed target version must be positive.');
        }
    }
}
