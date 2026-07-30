<?php

namespace Tests\Unit\Modules\ContactsLeads;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;
use Appart\Modules\ContactsLeads\Domain\Policy\LeadDeduplicationPolicy;
use Appart\Modules\ContactsLeads\Domain\Policy\LeadSignaturePolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactCoordinates;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentityId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentityType;
use DateTimeImmutable;

final class LeadValuesTest extends ContactsLeadsTestCase
{
    public function test_anonymous_identity_and_coordinates_are_distinct_and_normalized(): void
    {
        $visitor = VisitorIdentity::anonymous(VisitorIdentityId::fromString('a0000000-0000-4000-8000-000000000002'), '  Awa   Ndiaye ', ContactCoordinates::create(' AWA@EXAMPLE.TEST ', '+221 77 123 45 67'));
        self::assertSame(VisitorIdentityType::Anonymous, $visitor->type);
        self::assertSame('Awa Ndiaye', $visitor->name);
        self::assertSame('awa@example.test', $visitor->contacts->email);
        self::assertSame('+221771234567', $visitor->contacts->phone);
    }

    public function test_authenticated_identity_is_explicit(): void
    {
        $visitor = VisitorIdentity::authenticated(VisitorIdentityId::fromString('a0000000-0000-4000-8000-000000000003'), 'Awa Ndiaye', ContactCoordinates::create('awa@example.test', null));
        self::assertSame(VisitorIdentityType::Authenticated, $visitor->type);
    }

    public function test_contact_coordinate_is_required_and_validated(): void
    {
        $this->expectException(InvalidLeadValue::class);
        ContactCoordinates::create(null, null);
    }

    public function test_signature_uses_stable_identity_not_alternating_coordinates(): void
    {
        $id = VisitorIdentityId::fromString('a0000000-0000-4000-8000-000000000004');
        $email = VisitorIdentity::anonymous($id, 'Awa', ContactCoordinates::create('awa@example.test', null));
        $phone = VisitorIdentity::anonymous($id, 'Awa', ContactCoordinates::create(null, '+221771234567'));
        $policy = new LeadSignaturePolicy;
        $message = ContactMessage::fromString('Je souhaite davantage de renseignements.');
        self::assertSame($policy->create($this->listingId(), $email, ContactSubject::GeneralInquiry, $message)->value, $policy->create($this->listingId(), $phone, ContactSubject::GeneralInquiry, $message)->value);
    }

    public function test_rolling_window_crosses_calendar_boundaries(): void
    {
        $policy = new LeadDeduplicationPolicy;
        $first = LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:14:59+00:00'));
        $next = LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:15:00+00:00'));
        self::assertTrue($policy->conflicts($first, $next));
        self::assertFalse($policy->conflicts($first, LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:30:00+00:00'))));
    }
}
