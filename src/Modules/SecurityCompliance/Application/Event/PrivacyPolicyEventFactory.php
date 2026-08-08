<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class PrivacyPolicyEventFactory
{
    public function __construct(private PrivacyPolicyReaderV1 $reader) {}

    public function create(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyEventV1
    {
        $result = $this->reader->read($subject, $observedAt);

        return new PrivacyPolicyEventV1(PrivacyPolicyEventType::Observed, new PrivacyPolicyEventPayload(PrivacyPolicyEventStatus::from($result->status->value), $result->observedAt));
    }
}
