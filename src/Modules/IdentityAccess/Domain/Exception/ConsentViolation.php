<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class ConsentViolation extends IdentityAccessException
{
    public static function alreadyGranted(): self
    {
        return new self('Consent is already granted for this purpose.');
    }

    public static function notGranted(): self
    {
        return new self('Consent is not currently granted for this purpose.');
    }
}
