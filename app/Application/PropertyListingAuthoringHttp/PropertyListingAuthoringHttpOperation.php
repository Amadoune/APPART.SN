<?php

namespace App\Application\PropertyListingAuthoringHttp;

enum PropertyListingAuthoringHttpOperation: string
{
    case InitiateProperty = 'initiate_property';
    case ReadProperty = 'read_property';
    case PatchProperty = 'patch_property';
    case CreateListing = 'create_listing';
    case ReadDraft = 'read_draft';
    case PatchDraft = 'patch_draft';
    case AssessCompleteness = 'assess_completeness';
    case GrantDelegation = 'grant_delegation';
    case RevokeDelegation = 'revoke_delegation';
    case RequestSubmission = 'request_submission';
    case Portfolio = 'portfolio';

    public function isRead(): bool
    {
        return in_array($this, [
            self::ReadProperty,
            self::ReadDraft,
            self::AssessCompleteness,
            self::Portfolio,
        ], true);
    }
}
