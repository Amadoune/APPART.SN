<?php

namespace Appart\Modules\Media\Domain\Exception;

final class InvalidMediaValue extends MediaException
{
    public static function field(string $field): self
    {
        return new self("Invalid value for {$field}.");
    }
}
