<?php

namespace App\Application\ModerationResidualOperationalAuditContract;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOperationV1;

final readonly class ResidualOperationalAuditConversionV1
{
    public function __construct(
        public AdministrationAuditOperationV1 $operation,
        public string $subjectKey,
    ) {}
}
