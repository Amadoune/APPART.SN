<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

enum ContentSeoRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
