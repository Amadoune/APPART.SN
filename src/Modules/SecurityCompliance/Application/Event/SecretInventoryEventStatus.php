<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

enum SecretInventoryEventStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
