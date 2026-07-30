<?php

namespace App\Application\PropertyListingAuthoringOperations;

enum AuthoringOperation: string
{
    case InitiateProperty = 'InitiateProperty';
    case UpdateProperty = 'UpdateProperty';
    case CreateListing = 'CreateListing';
    case UpdateDraft = 'UpdateDraft';
    case GrantDelegation = 'GrantDelegation';
    case RevokeDelegation = 'RevokeDelegation';
    case SubmitListing = 'SubmitListing';
}
