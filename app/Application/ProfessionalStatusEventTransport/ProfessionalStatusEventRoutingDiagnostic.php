<?php

namespace App\Application\ProfessionalStatusEventTransport;

enum ProfessionalStatusEventRoutingDiagnostic: string
{
    case RouteUnavailable = 'route_unavailable';
    case TransferFailed = 'transfer_failed';
    case UnsupportedEvent = 'unsupported_event';
    case CorruptedEvent = 'corrupted_event';
}
