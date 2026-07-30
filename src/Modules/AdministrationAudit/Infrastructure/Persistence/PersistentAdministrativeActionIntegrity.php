<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

use RuntimeException;

final class PersistentAdministrativeActionIntegrity extends RuntimeException
{
    public static function invalid(string $component): self
    {
        return new self('Invalid persisted administrative action '.$component.'.');
    }
}
