<?php

namespace App\Application\PropertyListingAuthoringRuntime;

enum PropertyListingAuthoringRuntimeStatus: string
{
    case Ready = 'Ready';
    case MissingBinding = 'MissingBinding';
    case DependencyUnavailable = 'DependencyUnavailable';
    case IncompatibleVersion = 'IncompatibleVersion';
}
