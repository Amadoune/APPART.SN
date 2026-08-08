<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

enum AdministrationConsoleRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
