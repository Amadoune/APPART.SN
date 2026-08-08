<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result;

enum LeadContactConsentStatusV1: string
{
    case Granted = 'granted';
    case Denied = 'denied';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
