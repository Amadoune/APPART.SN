<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class IncidentEventFactory
{
    public function __construct(private IncidentReaderV1 $reader) {}

    public function create(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): IncidentEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new IncidentEventV1(IncidentEventType::Observed, new IncidentEventPayload(IncidentEventStatus::from($result->status->value), $result->observedAt));
    }
}
