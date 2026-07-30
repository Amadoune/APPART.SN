<?php

namespace App\Application\PublicProjectionOutbox;

enum PublicProjectionOutboxWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
    case InvalidTransition = 'invalid_transition';
    case ClaimMismatch = 'claim_mismatch';
    case NotFound = 'not_found';
}
