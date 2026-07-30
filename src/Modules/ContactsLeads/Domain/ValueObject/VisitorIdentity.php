<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class VisitorIdentity
{
    private function __construct(public VisitorIdentityId $id, public VisitorIdentityType $type, public string $name, public ContactCoordinates $contacts) {}

    public static function authenticated(VisitorIdentityId $accountId, string $name, ContactCoordinates $contacts): self
    {
        return new self($accountId, VisitorIdentityType::Authenticated, self::normalizeName($name), $contacts);
    }

    public static function anonymous(VisitorIdentityId $anonymousId, string $name, ContactCoordinates $contacts): self
    {
        return new self($anonymousId, VisitorIdentityType::Anonymous, self::normalizeName($name), $contacts);
    }

    private static function normalizeName(string $name): string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            throw InvalidLeadValue::field('visitor_name');
        }

        return $name;
    }
}
