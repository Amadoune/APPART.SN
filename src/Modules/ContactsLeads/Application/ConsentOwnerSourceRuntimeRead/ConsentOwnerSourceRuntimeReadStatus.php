<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead;

enum ConsentOwnerSourceRuntimeReadStatus: string
{
    case Granted = 'granted';
    case Denied = 'denied';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
