<?php

namespace App\Application\IdentityAccessEventOutbox;

enum IdentityAccessOutboxWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
}
