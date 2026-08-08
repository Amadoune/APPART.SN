<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

enum SecurityComplianceRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
