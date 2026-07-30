<?php

namespace App\Application\PublicProjectionStore;

enum PublicProjectionGenerationState: string
{
    case Active = 'active';
    case Candidate = 'candidate';
    case Retired = 'retired';
}
