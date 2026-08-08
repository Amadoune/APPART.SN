<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationAuditEventType: string
{
    case Observed = 'administration_console.audit.observed.v1';
}
