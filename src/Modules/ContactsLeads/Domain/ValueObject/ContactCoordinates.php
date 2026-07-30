<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class ContactCoordinates
{
    private function __construct(public ?string $email, public ?string $phone) {}

    public static function create(?string $email, ?string $phone): self
    {
        $email = $email === null ? null : strtolower(trim($email));
        $phone = $phone === null ? null : preg_replace('/[\s().-]+/', '', trim($phone));
        if ($email === null && $phone === null) {
            throw InvalidLeadValue::field('contact_coordinates');
        }
        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidLeadValue::field('visitor_email');
        }
        if ($phone !== null && preg_match('/^\+[1-9][0-9]{7,14}$/', $phone) !== 1) {
            throw InvalidLeadValue::field('visitor_phone');
        }

        return new self($email, $phone);
    }
}
