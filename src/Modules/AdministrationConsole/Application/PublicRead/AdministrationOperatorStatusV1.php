<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

enum AdministrationOperatorStatusV1: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
