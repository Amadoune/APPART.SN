<?php

namespace Appart\Modules\AdministrationAudit\Domain\Exception;

final class AdministrativeActionNotFound extends AdministrationAuditException
{
    public function __construct()
    {
        parent::__construct('The administrative action was not found.');
    }
}
