<?php

namespace App\Application\MediaIngestionEventDelivery;

enum MediaIngestionRetryDecision: string
{
    case Complete = 'Complete';
    case Retry = 'Retry';
    case Quarantine = 'Quarantine';
}
