<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

use InvalidArgumentException;

final readonly class AdministrationSubjectKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('The Administration subject key must be canonical.');
        }
    }

    public function canonical(): string
    {
        return $this->value;
    }
}
