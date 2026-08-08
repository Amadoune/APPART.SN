<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource;

enum AntiAbuseRevisionDecision: string
{
    case Allowed = 'allowed';
    case Blocked = 'blocked';
}
