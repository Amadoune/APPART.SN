<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class SecurityAuditEventFactory
{
    public function __construct(private SecurityAuditReaderV1 $reader) {}

    public function create(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecurityAuditEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new SecurityAuditEventV1(SecurityAuditEventType::Observed, new SecurityAuditEventPayload(SecurityAuditEventStatus::from($result->status->value), $result->observedAt));
    }
}
