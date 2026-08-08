<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum ResponsiveComplianceEventType: string
{
    case Observed = 'experience-acceptance.responsive-compliance.observed.v1';
}
