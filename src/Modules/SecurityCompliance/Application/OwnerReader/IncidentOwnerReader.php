<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class IncidentOwnerReader implements IncidentReaderV1
{
    public function __construct(private SecurityComplianceOwnerSource $source, private SecurityComplianceOwnerReaderV1 $policy) {}

    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): IncidentResultV1
    {
        $status = IncidentStatusV1::from($this->policy->incident($this->source->readIncident($subject, $observedAt))->status->value);

        return new IncidentResultV1($status, $observedAt);
    }
}
