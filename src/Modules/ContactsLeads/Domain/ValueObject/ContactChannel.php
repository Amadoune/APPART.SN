<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum ContactChannel: string
{
    case Email = 'email';
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
}
