<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource;

enum AntiAbuseRevisionReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
