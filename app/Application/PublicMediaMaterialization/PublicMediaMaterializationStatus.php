<?php

namespace App\Application\PublicMediaMaterialization;

enum PublicMediaMaterializationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case SourceMissing = 'source_missing';
    case SourceNotReady = 'source_not_ready';
    case SourceCorrupted = 'source_corrupted';
    case Divergent = 'divergent';
    case DependencyUnavailable = 'dependency_unavailable';
}
