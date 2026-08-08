<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum IncidentEventType: string
{
    case Observed = 'security-compliance.incident.observed.v1';
}
