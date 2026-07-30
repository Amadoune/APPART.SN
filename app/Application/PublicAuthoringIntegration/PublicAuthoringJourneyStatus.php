<?php

namespace App\Application\PublicAuthoringIntegration;

enum PublicAuthoringJourneyStatus: string
{
    case Succeeded = 'succeeded';
    case AcceptedReplay = 'accepted_replay';
    case NotFound = 'not_found';
    case Invalid = 'invalid';
    case Incomplete = 'incomplete';
    case Conflict = 'conflict';
    case Unavailable = 'unavailable';
}
