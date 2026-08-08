<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

enum AdministrationOperatorDeliveryStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
