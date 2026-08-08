<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead\Contract;

use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface SecretInventoryReaderV1
{
    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryResultV1;
}
