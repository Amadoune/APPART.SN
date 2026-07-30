<?php

namespace App\Application\ModerationAtomicOperation;

enum ModerationOutboxAppendResult: string
{
    case Stored = 'stored';
    case AlreadyStored = 'already_stored';
    case DivergentMessage = 'divergent_message';
    case Rejected = 'rejected';
}
