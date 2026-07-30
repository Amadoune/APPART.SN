<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class RoleAssignmentViolation extends IdentityAccessException
{
    public static function alreadyGranted(): self
    {
        return new self('The role is already granted.');
    }

    public static function notGranted(): self
    {
        return new self('The role is not currently granted.');
    }
}
