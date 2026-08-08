<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationOperatorEventType: string
{
    case Observed = 'administration_console.operator.observed.v1';
}
