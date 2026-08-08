<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum SecurityAuditEventType: string
{
    case Observed = 'security-compliance.security-audit.observed.v1';
}
