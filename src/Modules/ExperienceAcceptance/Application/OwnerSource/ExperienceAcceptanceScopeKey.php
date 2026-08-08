<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerSource;

use InvalidArgumentException;

final readonly class ExperienceAcceptanceScopeKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('Experience Acceptance scope key is invalid.');
        }
    }
}
