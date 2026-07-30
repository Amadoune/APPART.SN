<?php

namespace App\Application\MediaItemLifecycleEventTransport;

enum MediaItemLifecycleEventRoutingDiagnostic: string
{
    case RouteUnavailable = 'route_unavailable';
    case TransferFailed = 'transfer_failed';
    case UnsupportedEvent = 'unsupported_event';
    case CorruptedEvent = 'corrupted_event';
}
