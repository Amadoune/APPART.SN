<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class TemporalConsistencyViolation extends IdentityAccessException
{
    public static function nonIncreasingTime(): self
    {
        return new self('A domain change must occur after the preceding change.');
    }
}
