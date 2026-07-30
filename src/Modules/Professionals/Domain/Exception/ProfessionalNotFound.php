<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class ProfessionalNotFound extends ProfessionalsException
{
    public function __construct()
    {
        parent::__construct('The professional was not found.');
    }
}
