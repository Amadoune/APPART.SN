<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader;

enum ContentSeoOwnerReaderStatus: string
{
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Indexable = 'indexable';
    case NoIndex = 'no_index';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
