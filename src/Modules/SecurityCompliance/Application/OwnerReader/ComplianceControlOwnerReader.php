<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class ComplianceControlOwnerReader implements ComplianceControlReaderV1
{
    public function __construct(private SecurityComplianceOwnerSource $source, private SecurityComplianceOwnerReaderV1 $policy) {}

    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): ComplianceControlResultV1
    {
        $status = ComplianceControlStatusV1::from($this->policy->complianceControl($this->source->readComplianceControl($subject, $observedAt))->status->value);

        return new ComplianceControlResultV1($status, $observedAt);
    }
}
