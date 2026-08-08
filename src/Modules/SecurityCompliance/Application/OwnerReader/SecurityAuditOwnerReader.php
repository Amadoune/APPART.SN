<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class SecurityAuditOwnerReader implements SecurityAuditReaderV1
{
    public function __construct(private SecurityComplianceOwnerSource $source, private SecurityComplianceOwnerReaderV1 $policy) {}

    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecurityAuditResultV1
    {
        $status = SecurityAuditStatusV1::from($this->policy->securityAudit($this->source->readSecurityAudit($subject, $observedAt))->status->value);

        return new SecurityAuditResultV1($status, $observedAt);
    }
}
