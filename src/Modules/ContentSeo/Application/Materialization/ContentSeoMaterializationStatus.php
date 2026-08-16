<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

enum ContentSeoMaterializationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case SourceMissing = 'source_missing';
    case SourceNotReady = 'source_not_ready';
    case SourceCorrupted = 'source_corrupted';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
    case DependencyUnavailable = 'dependency_unavailable';
}
