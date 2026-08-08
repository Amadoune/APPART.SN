<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead\Contract;

use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface PrivacyPolicyReaderV1
{
    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): PrivacyPolicyResultV1;
}
