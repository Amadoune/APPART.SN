<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

enum AdministrativeActionLifecycleEventRoutingDiagnostic: string
{
    case RouteUnavailable = 'route_unavailable';
    case TransferFailed = 'transfer_failed';
    case UnsupportedEvent = 'unsupported_event';
    case CorruptedEvent = 'corrupted_event';
}
