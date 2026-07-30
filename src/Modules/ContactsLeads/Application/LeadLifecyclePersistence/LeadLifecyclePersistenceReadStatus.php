<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence;

enum LeadLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
