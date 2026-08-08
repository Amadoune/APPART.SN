<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead;

enum OperationalSeoStatusV1: string
{
    case Indexable = 'indexable';
    case NoIndex = 'no_index';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
