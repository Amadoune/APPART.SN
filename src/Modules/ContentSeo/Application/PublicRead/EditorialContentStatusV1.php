<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead;

enum EditorialContentStatusV1: string
{
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
