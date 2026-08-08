<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

enum ReliabilityOperationsReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
