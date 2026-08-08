<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

enum ComplianceControlDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
