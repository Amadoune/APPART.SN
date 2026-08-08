<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

use InvalidArgumentException;

final readonly class SecurityComplianceOutboxMessageId
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid SecurityCompliance outbox message ID.');
        }
    }
}
