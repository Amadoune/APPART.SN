<?php

namespace Appart\Modules\IdentityAccess\Domain\Exception;

final class InvalidIdentityValue extends IdentityAccessException
{
    public static function forField(string $field): self
    {
        return new self(sprintf('Invalid value for "%s".', $field));
    }
}
