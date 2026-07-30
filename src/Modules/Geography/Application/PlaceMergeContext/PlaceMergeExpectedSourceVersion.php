<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use InvalidArgumentException;

final readonly class PlaceMergeExpectedSourceVersion
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('The expected source version must be positive.');
        }
    }
}
