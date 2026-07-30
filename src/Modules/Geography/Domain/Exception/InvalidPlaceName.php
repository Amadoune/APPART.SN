<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class InvalidPlaceName extends GeographyException
{
    public static function fromValue(string $value): self
    {
        return new self(sprintf('A place name must contain between 2 and 120 characters; received "%s".', $value));
    }
}
