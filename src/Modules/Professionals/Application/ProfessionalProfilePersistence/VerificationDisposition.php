<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

enum VerificationDisposition: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
