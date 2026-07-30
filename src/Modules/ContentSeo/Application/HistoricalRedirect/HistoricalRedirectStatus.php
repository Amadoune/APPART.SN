<?php

namespace Appart\Modules\ContentSeo\Application\HistoricalRedirect;

enum HistoricalRedirectStatus: string
{
    case Resolved = 'resolved';
    case NotFound = 'not_found';
    case DestinationMissing = 'destination_missing';
    case LoopDetected = 'loop_detected';
    case ChainDetected = 'chain_detected';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
}
