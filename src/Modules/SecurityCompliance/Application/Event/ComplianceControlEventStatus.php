<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum ComplianceControlEventStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
