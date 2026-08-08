<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

use InvalidArgumentException;

final readonly class ReliabilityOperationsScopeKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('Reliability Operations scope key is invalid.');
        }
    }
}
