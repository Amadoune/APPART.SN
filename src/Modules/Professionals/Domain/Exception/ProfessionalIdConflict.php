<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class ProfessionalIdConflict extends ProfessionalsException
{
    public function __construct()
    {
        parent::__construct('The professional identity already exists.');
    }
}
