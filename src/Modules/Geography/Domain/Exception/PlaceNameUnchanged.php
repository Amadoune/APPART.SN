<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class PlaceNameUnchanged extends GeographyException
{
    public function __construct()
    {
        parent::__construct('The new official name is identical to the current name.');
    }
}
