<?php

namespace Appart\Modules\ContentSeo\Application\Event;

enum EditorialContentEventStatus: string
{
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
