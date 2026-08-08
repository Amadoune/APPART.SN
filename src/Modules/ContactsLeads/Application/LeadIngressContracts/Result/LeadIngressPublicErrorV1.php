<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

enum LeadIngressPublicErrorV1: string
{
    case InvalidRequest = 'invalid_request';
    case NotContactable = 'not_contactable';
    case RecipientUnavailable = 'recipient_unavailable';
    case Conflict = 'conflict';
    case NotFound = 'not_found';
    case Forbidden = 'forbidden';
    case DependencyUnavailable = 'dependency_unavailable';
    case InternalFailure = 'internal_failure';
}
