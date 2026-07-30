<?php

namespace Tests\Unit\Modules\Professionals;

use Appart\Modules\Professionals\Domain\Event\EstablishmentAdded;
use Appart\Modules\Professionals\Domain\Event\EstablishmentRemoved;
use Appart\Modules\Professionals\Domain\Event\MandateGranted;
use Appart\Modules\Professionals\Domain\Event\MandateRevoked;
use Appart\Modules\Professionals\Domain\Event\ProfessionalReactivated;
use Appart\Modules\Professionals\Domain\Event\ProfessionalRegistered;
use Appart\Modules\Professionals\Domain\Event\ProfessionalSuspended;
use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalViolation;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentName;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalName;
use Appart\Modules\Professionals\Domain\ValueObject\RegistrationNumber;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProfessionalTest extends TestCase
{
    public function test_it_registers_with_stable_identity_and_event(): void
    {
        $p = $this->professional();
        $events = $p->releaseEvents();
        self::assertSame($this->id()->value, $p->id()->value);
        self::assertSame('SN-NINEA/12345', $p->registrationNumber()->value);
        self::assertInstanceOf(ProfessionalRegistered::class, $events[0]);
        self::assertSame('Agence Horizon', $events[0]->name->value);
    }

    public function test_it_adds_an_owned_establishment(): void
    {
        $p = $this->professional();
        $p->releaseEvents();
        $p->addEstablishment($this->establishmentId(), $this->establishmentName(), $this->now());
        self::assertCount(1, $p->establishments());
        self::assertTrue($p->establishments()[0]->professionalId->equals($p->id()));
        self::assertInstanceOf(EstablishmentAdded::class, $p->releaseEvents()[0]);
    }

    public function test_duplicate_establishment_is_rejected(): void
    {
        $p = $this->withEstablishment();
        $this->expectException(ProfessionalViolation::class);
        $p->addEstablishment($this->establishmentId(), $this->establishmentName(), $this->now());
    }

    public function test_it_removes_but_retains_establishment_history(): void
    {
        $p = $this->withEstablishment();
        $p->releaseEvents();
        $p->removeEstablishment($this->establishmentId(), $this->later());
        self::assertFalse($p->establishments()[0]->isActive());
        self::assertNotNull($p->establishments()[0]->removedAt);
        self::assertInstanceOf(EstablishmentRemoved::class, $p->releaseEvents()[0]);
    }

    public function test_it_grants_a_mandate_for_an_active_establishment(): void
    {
        $p = $this->withEstablishment();
        $p->releaseEvents();
        $p->grantMandate($this->mandateId(), $this->establishmentId(), $this->representativeId(), $this->role(), $this->later());
        self::assertTrue($p->mandates()[0]->isActive());
        self::assertInstanceOf(MandateGranted::class, $p->releaseEvents()[0]);
    }

    public function test_duplicate_active_mandate_for_same_person_and_establishment_is_rejected(): void
    {
        $p = $this->withMandate();
        $this->expectException(ProfessionalViolation::class);
        $p->grantMandate(MandateId::fromString('40000000-0000-4000-8000-000000000099'), $this->establishmentId(), $this->representativeId(), MandateRole::fromString('manager'), $this->later());
    }

    public function test_same_representative_can_hold_mandates_for_different_establishments(): void
    {
        $p = $this->withMandate();
        $other = EstablishmentId::fromString('40000000-0000-4000-8000-000000000010');
        $p->addEstablishment($other, EstablishmentName::fromString('Agence annexe'), $this->later());
        $p->grantMandate(MandateId::fromString('40000000-0000-4000-8000-000000000011'), $other, $this->representativeId(), $this->role(), $this->later());
        self::assertCount(2, $p->mandates());
    }

    public function test_it_revokes_and_retains_mandate_history(): void
    {
        $p = $this->withMandate();
        $p->releaseEvents();
        $p->revokeMandate($this->mandateId(), $this->later());
        self::assertFalse($p->mandates()[0]->isActive());
        self::assertNotNull($p->mandates()[0]->revokedAt);
        self::assertInstanceOf(MandateRevoked::class, $p->releaseEvents()[0]);
    }

    public function test_revoked_mandate_cannot_be_revoked_twice(): void
    {
        $p = $this->withMandate();
        $p->revokeMandate($this->mandateId(), $this->later());
        $this->expectException(ProfessionalViolation::class);
        $p->revokeMandate($this->mandateId(), $this->later());
    }

    public function test_a_revoked_mandate_allows_a_new_historical_mandate(): void
    {
        $p = $this->withMandate();
        $p->revokeMandate($this->mandateId(), $this->later());
        $p->grantMandate(MandateId::fromString('40000000-0000-4000-8000-000000000099'), $this->establishmentId(), $this->representativeId(), $this->role(), $this->later());

        self::assertCount(2, $p->mandates());
        self::assertFalse($p->mandates()[0]->isActive());
        self::assertTrue($p->mandates()[1]->isActive());
    }

    public function test_establishment_with_active_mandate_cannot_be_removed(): void
    {
        $p = $this->withMandate();
        $this->expectException(ProfessionalViolation::class);
        $p->removeEstablishment($this->establishmentId(), $this->later());
    }

    public function test_a_removed_establishment_cannot_receive_a_mandate(): void
    {
        $p = $this->withEstablishment();
        $p->removeEstablishment($this->establishmentId(), $this->later());

        $this->expectException(ProfessionalViolation::class);
        $p->grantMandate($this->mandateId(), $this->establishmentId(), $this->representativeId(), $this->role(), $this->later());
    }

    public function test_it_suspends_and_reactivates_with_events(): void
    {
        $p = $this->professional();
        $p->releaseEvents();
        $p->suspend($this->now());
        self::assertTrue($p->isSuspended());
        self::assertInstanceOf(ProfessionalSuspended::class, $p->releaseEvents()[0]);
        $p->reactivate($this->later());
        self::assertFalse($p->isSuspended());
        self::assertInstanceOf(ProfessionalReactivated::class, $p->releaseEvents()[0]);
    }

    public function test_suspended_professional_cannot_receive_mandate(): void
    {
        $p = $this->withEstablishment();
        $p->suspend($this->later());
        $this->expectException(ProfessionalViolation::class);
        $p->grantMandate($this->mandateId(), $this->establishmentId(), $this->representativeId(), $this->role(), $this->later());
    }

    public function test_suspension_cannot_be_repeated(): void
    {
        $p = $this->professional();
        $p->suspend($this->now());

        $this->expectException(ProfessionalViolation::class);
        $p->suspend($this->now());
    }

    public function test_active_professional_cannot_be_reactivated(): void
    {
        $this->expectException(ProfessionalViolation::class);
        $this->professional()->reactivate($this->now());
    }

    public function test_past_change_is_rejected(): void
    {
        $p = $this->professional();
        $this->expectException(InvalidProfessionalValue::class);
        $p->suspend(new DateTimeImmutable('2026-07-16T11:00:00+00:00'));
    }

    public function test_release_events_empties_collection(): void
    {
        $p = $this->professional();
        self::assertNotEmpty($p->releaseEvents());
        self::assertSame([], $p->releaseEvents());
    }

    private function professional(): Professional
    {
        return Professional::register($this->id(), RegistrationNumber::fromString(' sn-ninea/12345 '), ProfessionalName::fromString(' Agence   Horizon '), $this->now());
    }

    private function withEstablishment(): Professional
    {
        $p = $this->professional();
        $p->addEstablishment($this->establishmentId(), $this->establishmentName(), $this->now());

        return $p;
    }

    private function withMandate(): Professional
    {
        $p = $this->withEstablishment();
        $p->grantMandate($this->mandateId(), $this->establishmentId(), $this->representativeId(), $this->role(), $this->now());

        return $p;
    }

    private function id(): ProfessionalId
    {
        return ProfessionalId::fromString('40000000-0000-4000-8000-000000000001');
    }

    private function establishmentId(): EstablishmentId
    {
        return EstablishmentId::fromString('40000000-0000-4000-8000-000000000002');
    }

    private function mandateId(): MandateId
    {
        return MandateId::fromString('40000000-0000-4000-8000-000000000003');
    }

    private function representativeId(): RepresentativeId
    {
        return RepresentativeId::fromString('representative:42');
    }

    private function establishmentName(): EstablishmentName
    {
        return EstablishmentName::fromString('Agence Dakar Centre');
    }

    private function role(): MandateRole
    {
        return MandateRole::fromString('director');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }

    private function later(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:01:00+00:00');
    }
}
