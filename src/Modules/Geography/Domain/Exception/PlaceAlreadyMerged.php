<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class PlaceAlreadyMerged extends GeographyException
{
    public function __construct()
    {
        parent::__construct('A merged place can no longer be changed or enabled.');
    }
}
