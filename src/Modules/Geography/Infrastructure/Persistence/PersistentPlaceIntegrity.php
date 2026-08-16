<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

use RuntimeException;

final class PersistentPlaceIntegrity extends RuntimeException
{
    public static function invalid(string $field): self
    {
        return new self('Persistent Place integrity failure: '.$field.'.');
    }
}
