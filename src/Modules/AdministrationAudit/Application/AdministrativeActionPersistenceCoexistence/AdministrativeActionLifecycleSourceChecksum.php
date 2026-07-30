<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleSourceChecksum
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('The historical source checksum must be a SHA-256 hexadecimal value.');
        }

        return new self($value);
    }
}
