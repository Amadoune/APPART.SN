<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

enum LeadIngressSubmissionStatusV1: string
{
    case Accepted = 'accepted';
    case AlreadyAccepted = 'already_accepted';
    case Rejected = 'rejected';
    case DivergentIntent = 'divergent_intent';
    case NotContactable = 'not_contactable';
    case ContactPrincipalUnavailable = 'contact_principal_unavailable';
    case RecipientNotEligible = 'recipient_not_eligible';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
