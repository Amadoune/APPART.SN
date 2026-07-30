<?php

namespace Appart\Modules\AdministrationAudit\Domain\Exception;

final class ConcurrentAdministrativeActionModification extends AdministrationAuditException
{
    public function __construct()
    {
        parent::__construct('The administrative action changed concurrently.');
    }
}
