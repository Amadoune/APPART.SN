<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class AccountNotFound extends IdentityAccessException
{
    public function __construct()
    {
        parent::__construct('The account was not found.');
    }
}
