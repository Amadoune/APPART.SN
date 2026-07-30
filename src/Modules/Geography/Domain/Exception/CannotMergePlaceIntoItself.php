<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class CannotMergePlaceIntoItself extends GeographyException
{
    public function __construct()
    {
        parent::__construct('A place cannot be merged into itself.');
    }
}
