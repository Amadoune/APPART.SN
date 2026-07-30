<?php

namespace Appart\Modules\ContactsLeads\Domain\Exception;

final class InvalidLeadValue extends LeadException
{
    public static function field(string $field): self
    {
        return new self("Invalid lead value: {$field}.");
    }
}
