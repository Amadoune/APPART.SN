<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class PrivacyPolicyOwnerReader implements PrivacyPolicyReaderV1
{
    public function __construct(private SecurityComplianceOwnerSource $source, private SecurityComplianceOwnerReaderV1 $policy) {}

    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyResultV1
    {
        $status = PrivacyPolicyStatusV1::from($this->policy->privacyPolicy($this->source->readPrivacyPolicy($subject, $observedAt))->status->value);

        return new PrivacyPolicyResultV1($status, $observedAt);
    }
}
