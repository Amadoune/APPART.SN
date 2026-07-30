<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

enum VerificationChannel: string
{
    case Email = 'email';
    case Phone = 'phone';
}
