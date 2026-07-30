<?php

namespace App\Application\ProfessionalProfileRuntime;

enum ProfessionalProfileRuntimeStatus: string
{
    case Ready = 'Ready';
    case MissingBinding = 'MissingBinding';
    case DependencyUnavailable = 'DependencyUnavailable';
    case IncompatibleVersion = 'IncompatibleVersion';
}
