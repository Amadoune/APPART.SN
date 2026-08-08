<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\PerformanceReadinessResultV1;

interface PerformanceReadinessReaderV1
{
    public function read(ExperienceAcceptanceObservedAt $observedAt): PerformanceReadinessResultV1;
}
