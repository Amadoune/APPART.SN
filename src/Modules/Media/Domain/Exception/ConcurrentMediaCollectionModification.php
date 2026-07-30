<?php

namespace Appart\Modules\Media\Domain\Exception;

final class ConcurrentMediaCollectionModification extends MediaException
{
    public function __construct()
    {
        parent::__construct('The media collection changed concurrently.');
    }
}
