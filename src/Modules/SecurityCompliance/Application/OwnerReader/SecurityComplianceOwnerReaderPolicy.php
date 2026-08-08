<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\ComplianceControlReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\IncidentReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\PrivacyPolicyReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecretInventoryReadResult;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityAuditReadResult;

final readonly class SecurityComplianceOwnerReaderPolicy implements SecurityComplianceOwnerReaderV1
{
    public function secretInventory(SecretInventoryReadResult $result): SecurityComplianceOwnerReaderResult
    {
        return $this->reduce($result->status->value);
    }

    public function securityAudit(SecurityAuditReadResult $result): SecurityComplianceOwnerReaderResult
    {
        return $this->reduce($result->status->value);
    }

    public function incident(IncidentReadResult $result): SecurityComplianceOwnerReaderResult
    {
        return $this->reduce($result->status->value);
    }

    public function privacyPolicy(PrivacyPolicyReadResult $result): SecurityComplianceOwnerReaderResult
    {
        return $this->reduce($result->status->value);
    }

    public function complianceControl(ComplianceControlReadResult $result): SecurityComplianceOwnerReaderResult
    {
        return $this->reduce($result->status->value);
    }

    private function reduce(string $status): SecurityComplianceOwnerReaderResult
    {
        return new SecurityComplianceOwnerReaderResult(SecurityComplianceOwnerReaderStatus::from($status));
    }
}
