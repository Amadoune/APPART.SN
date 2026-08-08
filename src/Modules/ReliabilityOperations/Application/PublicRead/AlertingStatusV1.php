<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

enum AlertingStatusV1: string
{
    case Ready = 'ready';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
