<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result;

enum LeadIngressAntiAbuseStatusV1: string
{
    case Allowed = 'allowed';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
