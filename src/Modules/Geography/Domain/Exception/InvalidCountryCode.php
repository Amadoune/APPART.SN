<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class InvalidCountryCode extends GeographyException
{
    public static function fromValue(string $value): self
    {
        return new self(sprintf('"%s" is not a two-letter country code.', $value));
    }
}
