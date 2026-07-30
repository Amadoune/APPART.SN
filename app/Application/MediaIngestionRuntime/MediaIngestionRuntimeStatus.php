<?php

namespace App\Application\MediaIngestionRuntime;

enum MediaIngestionRuntimeStatus: string
{
    case Ready = 'Ready';
    case MissingBinding = 'MissingBinding';
    case DependencyUnavailable = 'DependencyUnavailable';
    case IncompatibleVersion = 'IncompatibleVersion';
}
