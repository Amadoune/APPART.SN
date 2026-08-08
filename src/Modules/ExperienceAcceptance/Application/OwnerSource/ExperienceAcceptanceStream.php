<?php

namespace Appart\Modules\ExperienceAcceptance\Application\OwnerSource;

enum ExperienceAcceptanceStream: string
{
    case ResponsiveCompliance = 'responsive_compliance';
    case AccessibilityCompliance = 'accessibility_compliance';
    case UserExperience = 'user_experience';
    case EndToEndReadiness = 'end_to_end_readiness';
    case PerformanceReadiness = 'performance_readiness';
    case UserAcceptance = 'user_acceptance';
    case ReleaseCandidate = 'release_candidate';
}
