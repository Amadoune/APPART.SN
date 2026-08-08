<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum UserExperienceEventType: string
{
    case Observed = 'experience-acceptance.user-experience.observed.v1';
}
