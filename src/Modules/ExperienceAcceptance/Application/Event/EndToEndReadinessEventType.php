<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum EndToEndReadinessEventType: string
{
    case Observed = 'experience-acceptance.end-to-end-readiness.observed.v1';
}
