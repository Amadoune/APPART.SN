<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationOperatorEventStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
