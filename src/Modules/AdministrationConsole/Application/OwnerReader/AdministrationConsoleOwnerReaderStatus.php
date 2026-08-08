<?php

namespace Appart\Modules\AdministrationConsole\Application\OwnerReader;

enum AdministrationConsoleOwnerReaderStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Ready = 'ready';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
