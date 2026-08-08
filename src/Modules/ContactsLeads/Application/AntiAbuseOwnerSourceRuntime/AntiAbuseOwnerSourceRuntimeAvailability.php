<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime;

enum AntiAbuseOwnerSourceRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
