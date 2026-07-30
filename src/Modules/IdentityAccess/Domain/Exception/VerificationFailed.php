<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class VerificationFailed extends IdentityAccessException
{
    public static function invalidToken(): self
    {
        return new self('The verification token is invalid.');
    }

    public static function expiredToken(): self
    {
        return new self('The verification token has expired.');
    }

    public static function alreadyVerified(): self
    {
        return new self('This identity channel is already verified.');
    }

    public static function wrongChannel(): self
    {
        return new self('The verification token belongs to another channel.');
    }
}
