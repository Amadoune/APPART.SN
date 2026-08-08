<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\AccessibilityComplianceResultV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

interface AccessibilityComplianceReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): AccessibilityComplianceResultV1;
}
