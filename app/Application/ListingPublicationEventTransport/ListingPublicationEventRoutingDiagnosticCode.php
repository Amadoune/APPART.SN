<?php

namespace App\Application\ListingPublicationEventTransport;

enum ListingPublicationEventRoutingDiagnosticCode: string
{
    case RouteUnavailable = 'route_unavailable';
    case TransferFailed = 'transfer_failed';
    case UnsupportedEvent = 'unsupported_event';
    case CorruptedEvent = 'corrupted_event';
}
