<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

enum ExperienceAcceptanceOutboxMessageType: string
{
    case ResponsiveCompliance = 'experience-acceptance.responsive-compliance.observed.v1';
    case AccessibilityCompliance = 'experience-acceptance.accessibility-compliance.observed.v1';
    case UserExperience = 'experience-acceptance.user-experience.observed.v1';
    case EndToEndReadiness = 'experience-acceptance.end-to-end-readiness.observed.v1';
    case PerformanceReadiness = 'experience-acceptance.performance-readiness.observed.v1';
    case UserAcceptance = 'experience-acceptance.user-acceptance.observed.v1';
    case ReleaseCandidate = 'experience-acceptance.release-candidate.observed.v1';
}
