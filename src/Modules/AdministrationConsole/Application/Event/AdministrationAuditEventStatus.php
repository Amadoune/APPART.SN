<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

enum AdministrationAuditEventStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
