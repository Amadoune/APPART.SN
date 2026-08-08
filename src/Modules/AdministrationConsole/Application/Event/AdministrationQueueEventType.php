<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationQueueEventType: string
{
    case Observed = 'administration_console.queue.observed.v1';
}
