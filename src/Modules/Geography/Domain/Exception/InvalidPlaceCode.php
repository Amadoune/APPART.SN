<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class InvalidPlaceCode extends GeographyException
{
    public static function fromValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid official place code.', $value));
    }
}
