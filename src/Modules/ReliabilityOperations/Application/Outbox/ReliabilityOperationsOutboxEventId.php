<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

use InvalidArgumentException;

final readonly class ReliabilityOperationsOutboxEventId
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid ReliabilityOperations outbox event ID.');
        }
    }
}
