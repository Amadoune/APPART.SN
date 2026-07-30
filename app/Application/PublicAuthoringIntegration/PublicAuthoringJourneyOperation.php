<?php

namespace App\Application\PublicAuthoringIntegration;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;

enum PublicAuthoringJourneyOperation: string
{
    case InitiateProperty = 'initiate-property';
    case UpdateProperty = 'update-property';
    case CreateListing = 'create-listing';
    case UpdateDraft = 'update-draft';
    case GrantDelegation = 'grant-delegation';
    case RevokeDelegation = 'revoke-delegation';
    case SubmitListing = 'submit-listing';

    public function authoringOperation(): AuthoringOperation
    {
        return AuthoringOperation::from(match ($this) {
            self::InitiateProperty => AuthoringOperation::InitiateProperty->value,
            self::UpdateProperty => AuthoringOperation::UpdateProperty->value,
            self::CreateListing => AuthoringOperation::CreateListing->value,
            self::UpdateDraft => AuthoringOperation::UpdateDraft->value,
            self::GrantDelegation => AuthoringOperation::GrantDelegation->value,
            self::RevokeDelegation => AuthoringOperation::RevokeDelegation->value,
            self::SubmitListing => AuthoringOperation::SubmitListing->value,
        });
    }
}
