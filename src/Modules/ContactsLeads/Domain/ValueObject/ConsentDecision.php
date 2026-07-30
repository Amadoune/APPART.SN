<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum ConsentDecision: string
{
    case Granted = 'granted';
    case Denied = 'denied';
}
