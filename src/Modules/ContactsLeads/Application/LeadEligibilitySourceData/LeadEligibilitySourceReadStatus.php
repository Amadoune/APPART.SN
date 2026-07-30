<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData;

enum LeadEligibilitySourceReadStatus: string
{
    case Found = 'found';
    case SourceAbsent = 'source_absent';
    case Corrupted = 'corrupted';
}
