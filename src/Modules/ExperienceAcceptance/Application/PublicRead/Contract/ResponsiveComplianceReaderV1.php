<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ResponsiveComplianceResultV1;

interface ResponsiveComplianceReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): ResponsiveComplianceResultV1;
}
