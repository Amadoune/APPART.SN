<?php

namespace App\Application\PublicProjectionOutbox;

enum PublicProjectionOutboxClaimState: string
{
    case Unclaimed = 'unclaimed';
    case Claimed = 'claimed';
    case Released = 'released';
    case Expired = 'expired';
    case Abandoned = 'abandoned';
}
