<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum ReleaseCandidateEventType: string
{
    case Observed = 'experience-acceptance.release-candidate.observed.v1';
}
