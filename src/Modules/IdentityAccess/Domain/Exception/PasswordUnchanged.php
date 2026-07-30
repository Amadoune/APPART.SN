<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class PasswordUnchanged extends IdentityAccessException
{
    public function __construct()
    {
        parent::__construct('The new password hash must differ from the current hash.');
    }
}
