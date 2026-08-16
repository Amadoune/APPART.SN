<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

enum ContentSeoMaterializationSourceStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case NotReady = 'not_ready';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
