<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum VisitorIdentityType: string
{
    case Authenticated = 'authenticated';
    case Anonymous = 'anonymous';
}
