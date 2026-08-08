<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead\Contract;

use Appart\Modules\SecurityCompliance\Application\PublicRead\DataExportResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

interface DataExportReaderV1
{
    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): DataExportResultV1;
}
