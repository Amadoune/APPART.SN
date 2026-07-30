<?php

namespace Appart\Modules\AdministrationAudit\Domain\Exception;

final class InvalidAuditValue extends AdministrationAuditException
{
    public static function field(string $field): self
    {
        return new self("Invalid value for {$field}.");
    }
}
