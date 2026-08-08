<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class ComplianceControlEventFactory
{
    public function __construct(private ComplianceControlReaderV1 $reader) {}

    public function create(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): ComplianceControlEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new ComplianceControlEventV1(ComplianceControlEventType::Observed, new ComplianceControlEventPayload(ComplianceControlEventStatus::from($result->status->value), $result->observedAt));
    }
}
