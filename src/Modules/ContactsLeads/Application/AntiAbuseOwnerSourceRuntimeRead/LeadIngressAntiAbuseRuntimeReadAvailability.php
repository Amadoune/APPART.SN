<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead;

enum LeadIngressAntiAbuseRuntimeReadAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
