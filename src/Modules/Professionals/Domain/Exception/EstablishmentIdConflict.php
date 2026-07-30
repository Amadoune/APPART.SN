<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class EstablishmentIdConflict extends ProfessionalsException
{
    public function __construct()
    {
        parent::__construct('The establishment identity already belongs to another professional.');
    }
}
