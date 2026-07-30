<?php

namespace Appart\Modules\Media\Domain\Exception;

final class MediaCollectionNotFound extends MediaException
{
    public function __construct()
    {
        parent::__construct('The media collection was not found.');
    }
}
