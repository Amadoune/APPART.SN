<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use RuntimeException;

final class PersistentPropertyIntegrity extends RuntimeException
{
    public static function invalid(string $component): self
    {
        return new self('Persistent Property data is inconsistent: '.$component.'.');
    }
}
