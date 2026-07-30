<?php

namespace Appart\Modules\Media\Domain\Exception;

final class PropertyUnavailable extends MediaException
{
    public function __construct()
    {
        parent::__construct('The property does not exist.');
    }
}
