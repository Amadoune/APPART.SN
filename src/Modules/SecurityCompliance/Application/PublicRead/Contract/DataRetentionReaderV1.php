<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead\Contract;

use Appart\Modules\SecurityCompliance\Application\PublicRead\DataRetentionResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface DataRetentionReaderV1
{
    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): DataRetentionResultV1;
}
