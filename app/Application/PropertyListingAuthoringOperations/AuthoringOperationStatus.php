<?php

namespace App\Application\PropertyListingAuthoringOperations;

enum AuthoringOperationStatus: string
{
    case Applied = 'Applied';
    case AlreadyApplied = 'AlreadyApplied';
    case DivergentIntent = 'DivergentIntent';
    case NotFoundOrForbidden = 'NotFoundOrForbidden';
    case Invalid = 'Invalid';
    case Incomplete = 'Incomplete';
    case ConcurrentModification = 'ConcurrentModification';
    case LifecycleConflict = 'LifecycleConflict';
    case DependencyUnavailable = 'DependencyUnavailable';
}
