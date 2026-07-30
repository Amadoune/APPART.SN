<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class PlaceAlreadyDisabled extends GeographyException
{
    public function __construct()
    {
        parent::__construct('The place is already disabled.');
    }
}
