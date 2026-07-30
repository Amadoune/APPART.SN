<?php

namespace App\Application\MediaIngestionEventIntegration;

enum MediaIngestionAtomicDeliveryResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Rejected = 'rejected';
}
