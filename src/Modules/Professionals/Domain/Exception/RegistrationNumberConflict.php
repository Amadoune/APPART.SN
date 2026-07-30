<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class RegistrationNumberConflict extends ProfessionalsException
{
    public function __construct()
    {
        parent::__construct('The professional registration number already exists.');
    }
}
