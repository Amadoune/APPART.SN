<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class PlaceAlreadyEnabled extends GeographyException
{
    public function __construct()
    {
        parent::__construct('The place is already enabled.');
    }
}
