<?php

namespace Appart\Modules\Media\Domain\Exception;

final class MediaIdConflict extends MediaException
{
    public function __construct()
    {
        parent::__construct('The media identity already belongs to another collection.');
    }
}
