<?php

namespace Appart\Modules\Professionals\Domain\Exception;

final class InvalidProfessionalValue extends ProfessionalsException
{
    public static function field(string $field): self
    {
        return new self("Invalid value for {$field}.");
    }
}
