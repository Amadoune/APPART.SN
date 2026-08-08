<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

enum SecurityAuditDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
