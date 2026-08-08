<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum ComplianceControlEventType: string
{
    case Observed = 'security-compliance.compliance-control.observed.v1';
}
