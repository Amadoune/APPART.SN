<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

enum ObservabilityStatusV1: string
{
    case Available = 'available';
    case Degraded = 'degraded';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
