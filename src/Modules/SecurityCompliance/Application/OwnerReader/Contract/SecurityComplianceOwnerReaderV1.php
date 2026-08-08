<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\SecurityComplianceOwnerReaderResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditReadResult;

interface SecurityComplianceOwnerReaderV1
{
    public function secretInventory(SecretInventoryReadResult $result): SecurityComplianceOwnerReaderResult;

    public function securityAudit(SecurityAuditReadResult $result): SecurityComplianceOwnerReaderResult;

    public function incident(IncidentReadResult $result): SecurityComplianceOwnerReaderResult;

    public function privacyPolicy(PrivacyPolicyReadResult $result): SecurityComplianceOwnerReaderResult;

    public function complianceControl(ComplianceControlReadResult $result): SecurityComplianceOwnerReaderResult;
}
