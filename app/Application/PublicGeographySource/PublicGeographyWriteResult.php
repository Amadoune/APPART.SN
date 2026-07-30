<?php

namespace App\Application\PublicGeographySource;

enum PublicGeographyWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
}
