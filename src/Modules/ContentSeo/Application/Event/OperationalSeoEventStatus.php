<?php

namespace Appart\Modules\ContentSeo\Application\Event;

enum OperationalSeoEventStatus: string
{
    case Indexable = 'indexable';
    case NoIndex = 'no_index';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
