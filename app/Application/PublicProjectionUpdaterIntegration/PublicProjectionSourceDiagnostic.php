<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

enum PublicProjectionSourceDiagnostic: string
{
    case None = 'none';
    case InvalidIdentity = 'invalid_identity';
    case Corrupted = 'corrupted';
    case MediaOwnershipMissing = 'media_ownership_missing';
    case MediaOwnershipAmbiguous = 'media_ownership_ambiguous';
}
