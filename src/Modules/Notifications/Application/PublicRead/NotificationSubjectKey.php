<?php

namespace Appart\Modules\Notifications\Application\PublicRead;

use InvalidArgumentException;

final readonly class NotificationSubjectKey
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Notification subject key must not be empty.');
        }

        $this->value = $value;
    }
}
