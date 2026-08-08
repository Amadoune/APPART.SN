<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead\Contract;

use Appart\Modules\SecurityCompliance\Application\PublicRead\CryptographyPolicyResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface CryptographyPolicyReaderV1
{
    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): CryptographyPolicyResultV1;
}
