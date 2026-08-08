<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum PerformanceReadinessEventType: string
{
    case Observed = 'experience-acceptance.performance-readiness.observed.v1';
}
