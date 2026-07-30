<?php

namespace App\Application\PublicProjectionRebuild;

enum PublicProjectionGenerationTransition: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case GenerationNotFound = 'generation_not_found';
    case InvalidState = 'invalid_state';
    case ValidationFailed = 'validation_failed';
}
