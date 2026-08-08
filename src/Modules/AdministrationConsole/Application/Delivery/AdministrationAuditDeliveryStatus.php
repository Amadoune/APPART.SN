<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

enum AdministrationAuditDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
