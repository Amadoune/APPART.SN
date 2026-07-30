<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class ConcurrentProfessionalModification extends ProfessionalsException
{
    public function __construct()
    {
        parent::__construct('The professional changed concurrently.');
    }
}
