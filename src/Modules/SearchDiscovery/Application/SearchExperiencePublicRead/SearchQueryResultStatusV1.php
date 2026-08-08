<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead;

enum SearchQueryResultStatusV1: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
