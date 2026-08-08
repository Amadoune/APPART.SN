<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

enum EditorialContentDeliveryStatus: string
{
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
