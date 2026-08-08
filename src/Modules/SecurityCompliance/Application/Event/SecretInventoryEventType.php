<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum SecretInventoryEventType: string
{
    case Observed = 'security-compliance.secret-inventory.observed.v1';
}
