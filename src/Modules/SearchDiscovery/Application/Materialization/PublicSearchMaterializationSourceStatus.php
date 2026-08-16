<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

enum PublicSearchMaterializationSourceStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
