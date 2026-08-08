<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class SecretInventoryEventFactory
{
    public function __construct(private SecretInventoryReaderV1 $reader) {}

    public function create(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new SecretInventoryEventV1(SecretInventoryEventType::Observed, new SecretInventoryEventPayload(SecretInventoryEventStatus::from($result->status->value), $result->observedAt));
    }
}
