<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use RuntimeException;
use Throwable;

final class PersistentMediaCollectionIntegrity extends RuntimeException
{
    public static function invalid(string $component, ?Throwable $previous = null): self
    {
        return new self('Persistent MediaCollection data is inconsistent: '.$component.'.', 0, $previous);
    }
}
