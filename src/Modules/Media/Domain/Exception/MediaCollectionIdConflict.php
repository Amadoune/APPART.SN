<?php

namespace Appart\Modules\Media\Domain\Exception;

final class MediaCollectionIdConflict extends MediaException
{
    public function __construct()
    {
        parent::__construct('The media collection identity already exists.');
    }
}
