<?php

namespace App\Application\MultiTargetDelivery;

enum MultiTargetPropagationStatus: string
{
    case TargetsAvailable = 'targets_available';
    case NoTargets = 'no_targets';
    case Completed = 'completed';
    case InvalidIdentity = 'invalid_identity';
    case Corrupted = 'corrupted';
}
