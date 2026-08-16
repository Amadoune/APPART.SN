<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

enum PublicSearchMaterializationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case SourceMissing = 'source_missing';
    case SourceCorrupted = 'source_corrupted';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
    case DependencyUnavailable = 'dependency_unavailable';
}
