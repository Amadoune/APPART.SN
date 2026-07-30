<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation;

enum CreateListingDraftStatusV1: string
{
    case Applied = 'Applied';
    case AlreadyApplied = 'AlreadyApplied';
    case DivergentIntent = 'DivergentIntent';
    case PropertyUnavailable = 'PropertyUnavailable';
    case ListingIdConflict = 'ListingIdConflict';
    case InvalidCommand = 'InvalidCommand';
    case DependencyUnavailable = 'DependencyUnavailable';
    case PersistenceFailure = 'PersistenceFailure';
}
