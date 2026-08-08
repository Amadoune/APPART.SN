<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum UserAcceptanceEventType: string
{
    case Observed = 'experience-acceptance.user-acceptance.observed.v1';
}
