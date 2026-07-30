<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class InvalidHistoricalAccountPersistenceState extends IdentityAccessException
{
    public static function field(string $field): self
    {
        return new self(sprintf('Invalid historical Account persistence snapshot field "%s".', $field));
    }
}
