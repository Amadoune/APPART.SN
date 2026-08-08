<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

enum LeadIngressReadStatusV1: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Forbidden = 'forbidden';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
