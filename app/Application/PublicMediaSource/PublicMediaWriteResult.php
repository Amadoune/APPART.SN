<?php

namespace App\Application\PublicMediaSource;

enum PublicMediaWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
}
