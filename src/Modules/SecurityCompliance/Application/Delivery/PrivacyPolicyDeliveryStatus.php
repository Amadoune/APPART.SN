<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

enum PrivacyPolicyDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
