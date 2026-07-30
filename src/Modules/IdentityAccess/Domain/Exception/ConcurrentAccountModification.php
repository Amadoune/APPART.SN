<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class ConcurrentAccountModification extends IdentityAccessException
{
    public function __construct()
    {
        parent::__construct('The account changed concurrently.');
    }
}
