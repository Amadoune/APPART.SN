<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

enum ResponsiveComplianceDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
