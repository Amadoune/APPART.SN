<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

enum OperationalSeoDeliveryStatus: string
{
    case Indexable = 'indexable';
    case NoIndex = 'no_index';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
