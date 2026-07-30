<?php

namespace Appart\Modules\Geography\Domain\Exception;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final class DuplicatePlaceId extends GeographyException
{
    public static function forId(PlaceId $id): self
    {
        return new self(sprintf('A place with identifier "%s" already exists.', $id->value));
    }
}
