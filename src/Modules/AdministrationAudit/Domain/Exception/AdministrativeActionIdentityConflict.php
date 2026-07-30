<?php

namespace Appart\Modules\AdministrationAudit\Domain\Exception;

final class AdministrativeActionIdentityConflict extends AdministrationAuditException
{
    public function __construct()
    {
        parent::__construct('The administrative action identity already exists.');
    }
}
