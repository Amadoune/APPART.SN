<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

use InvalidArgumentException;

final readonly class LegacyMigrationSubjectKey
{
    public function __construct(public string $value)
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('The Legacy Migration subject key must be canonical.');
        }
    }

    public function canonical(): string
    {
        return $this->value;
    }
}
