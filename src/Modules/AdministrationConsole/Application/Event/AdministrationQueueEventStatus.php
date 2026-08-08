<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationQueueEventStatus: string
{
    case Ready = 'ready';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
