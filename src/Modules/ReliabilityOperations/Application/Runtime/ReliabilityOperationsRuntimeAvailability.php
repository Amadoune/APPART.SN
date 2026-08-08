<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

enum ReliabilityOperationsRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
