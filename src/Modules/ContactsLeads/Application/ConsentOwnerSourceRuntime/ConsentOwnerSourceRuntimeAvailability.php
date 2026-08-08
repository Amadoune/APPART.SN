<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime;

enum ConsentOwnerSourceRuntimeAvailability: string
{
    case Available = 'available';
    case DependencyUnavailable = 'dependency_unavailable';
    case Corrupted = 'corrupted';
}
