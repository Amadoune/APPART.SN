<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

use InvalidArgumentException;

final readonly class ExperienceAcceptanceOutboxMessageId
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid ExperienceAcceptance outbox message ID.');
        }
    }
}
