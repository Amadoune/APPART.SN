<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Routing;

enum ExperienceAcceptanceRouteDestination: string
{
    case ResponsiveCompliance = 'experience-acceptance.responsive-compliance.v1';
    case AccessibilityCompliance = 'experience-acceptance.accessibility-compliance.v1';
    case UserExperience = 'experience-acceptance.user-experience.v1';
    case EndToEndReadiness = 'experience-acceptance.end-to-end-readiness.v1';
    case PerformanceReadiness = 'experience-acceptance.performance-readiness.v1';
    case UserAcceptance = 'experience-acceptance.user-acceptance.v1';
    case ReleaseCandidate = 'experience-acceptance.release-candidate.v1';
}
