<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum AccessibilityComplianceEventType: string
{
    case Observed = 'experience-acceptance.accessibility-compliance.observed.v1';
}
