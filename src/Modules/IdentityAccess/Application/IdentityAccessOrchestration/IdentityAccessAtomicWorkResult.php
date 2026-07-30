<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

enum IdentityAccessAtomicWorkResult: string
{
    case Applied = 'Applied';
    case Rejected = 'Rejected';
}
