<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class AccountStateViolation extends IdentityAccessException
{
    public static function suspended(): self
    {
        return new self('The account is suspended.');
    }

    public static function alreadySuspended(): self
    {
        return new self('The account is already suspended.');
    }

    public static function alreadyActive(): self
    {
        return new self('The account is already active.');
    }

    public static function invalidReconstitution(): self
    {
        return new self('The persisted account state is invalid.');
    }
}
