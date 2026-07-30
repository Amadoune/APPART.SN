<?php

namespace Appart\Modules\Geography\Domain\Exception;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final class PlaceNotFound extends GeographyException
{
    public static function forId(PlaceId $id): self
    {
        return new self(sprintf('Place "%s" was not found.', $id->value));
    }
}
