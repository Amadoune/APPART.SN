<?php

namespace Appart\Modules\AdministrationConsole\Application\PublicRead;

enum AdministrationQueueStatusV1: string
{
    case Ready = 'ready';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
