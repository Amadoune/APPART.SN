<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum PrivacyPolicyEventType: string
{
    case Observed = 'security-compliance.privacy-policy.observed.v1';
}
