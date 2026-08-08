<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSource;

enum ConsentRevisionDecision: string
{
    case Undecided = 'undecided';
    case Granted = 'granted';
    case Denied = 'denied';
    case Withdrawn = 'withdrawn';
}
