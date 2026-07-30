<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class DuplicateAccountIdentity extends IdentityAccessException
{
    public static function accountId(): self
    {
        return new self('The account identifier already exists.');
    }

    public static function email(): self
    {
        return new self('The email address already belongs to an account.');
    }

    public static function phone(): self
    {
        return new self('The phone number already belongs to an account.');
    }
}
