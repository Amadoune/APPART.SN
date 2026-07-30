<?php

namespace Tests\Unit\Modules\Professionals;

use Appart\Modules\Professionals\Application\UseCase\AddEstablishment;
use Appart\Modules\Professionals\Application\UseCase\GrantMandate;
use Appart\Modules\Professionals\Application\UseCase\ReactivateProfessional;
use Appart\Modules\Professionals\Application\UseCase\RegisterProfessional;
use Appart\Modules\Professionals\Application\UseCase\RemoveEstablishment;
use Appart\Modules\Professionals\Application\UseCase\RevokeMandate;
use Appart\Modules\Professionals\Application\UseCase\SuspendProfessional;
use Appart\Modules\Professionals\Domain\Exception\ConcurrentProfessionalModification;
use Appart\Modules\Professionals\Domain\Exception\EstablishmentIdConflict;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalIdConflict;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalNotFound;
use Appart\Modules\Professionals\Domain\Exception\RegistrationNumberConflict;
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
use Tests\Unit\Modules\Professionals\Support\FakeProfessionalRegistry;

final class ProfessionalUseCasesTest extends TestCase
{
    public function test_all_use_cases_orchestrate_the_lifecycle(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        (new AddEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->establishmentName(), $this->now());
        (new GrantMandate($r))->execute($this->id(), $this->mandateId(), $this->establishmentId(), $this->representativeId(), $this->role(), $this->now());
        (new RevokeMandate($r))->execute($this->id(), $this->mandateId(), $this->now());
        (new RemoveEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->now());
        (new SuspendProfessional($r))->execute($this->id(), $this->now());
        (new ReactivateProfessional($r))->execute($this->id(), $this->now());
        $stored = $r->find($this->id());
        self::assertNotNull($stored);
        self::assertFalse($stored->isSuspended());
        self::assertSame(6, $stored->version());
    }

    public function test_unknown_professional_is_reported(): void
    {
        $this->expectException(ProfessionalNotFound::class);
        (new SuspendProfessional(new FakeProfessionalRegistry))->execute($this->id(), $this->now());
    }

    public function test_duplicate_identity_is_explicit(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $this->expectException(ProfessionalIdConflict::class);
        $this->register($r);
    }

    public function test_duplicate_registration_number_is_explicit(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $this->expectException(RegistrationNumberConflict::class);
        (new RegisterProfessional($r))->execute(ProfessionalId::fromString('40000000-0000-4000-8000-000000000099'), $this->registration(), ProfessionalName::fromString('Autre agence'), $this->now());
    }

    public function test_stale_save_is_rejected(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $first = $r->find($this->id());
        $stale = $r->find($this->id());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $first->suspend($this->now());
        $r->save($first, 0);
        $stale->addEstablishment($this->establishmentId(), $this->establishmentName(), $this->now());
        $this->expectException(ConcurrentProfessionalModification::class);
        $r->save($stale, 0);
    }

    public function test_failed_save_does_not_expose_mutation(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $r->failNextSave();
        try {
            (new SuspendProfessional($r))->execute($this->id(), $this->now());
            self::fail();
        } catch (ConcurrentProfessionalModification) {
            $stored = $r->find($this->id());
            self::assertNotNull($stored);
            self::assertFalse($stored->isSuspended());
            self::assertSame(0, $stored->version());
        }
    }

    public function test_two_professionals_cannot_own_the_same_establishment(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $this->registerSecond($r);
        (new AddEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->establishmentName(), $this->now());

        $this->expectException(EstablishmentIdConflict::class);
        (new AddEstablishment($r))->execute($this->secondId(), $this->establishmentId(), EstablishmentName::fromString('Agence concurrente'), $this->now());
    }

    public function test_establishment_reservation_remains_with_historical_owner_after_removal(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $this->registerSecond($r);
        (new AddEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->establishmentName(), $this->now());
        (new RemoveEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->now());

        $this->expectException(EstablishmentIdConflict::class);
        (new AddEstablishment($r))->execute($this->secondId(), $this->establishmentId(), EstablishmentName::fromString('Agence concurrente'), $this->now());
    }

    public function test_failed_establishment_save_leaks_neither_mutation_nor_reservation(): void
    {
        $r = new FakeProfessionalRegistry;
        $this->register($r);
        $this->registerSecond($r);
        $r->failNextSave();

        try {
            (new AddEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->establishmentName(), $this->now());
            self::fail();
        } catch (ConcurrentProfessionalModification) {
            self::assertSame([], $r->find($this->id())?->establishments());
        }

        (new AddEstablishment($r))->execute($this->secondId(), $this->establishmentId(), EstablishmentName::fromString('Agence légitime'), $this->now());
        self::assertCount(1, $r->find($this->secondId())?->establishments() ?? []);
    }

    public function test_saved_and_reloaded_aggregates_never_replay_domain_events(): void
    {
        $r = new FakeProfessionalRegistry;
        $registered = (new RegisterProfessional($r))->execute($this->id(), $this->registration(), ProfessionalName::fromString('Agence Horizon'), $this->now());
        self::assertCount(1, $registered->releaseEvents());
        self::assertSame([], $r->find($this->id())?->releaseEvents());

        $changed = (new AddEstablishment($r))->execute($this->id(), $this->establishmentId(), $this->establishmentName(), $this->now());
        self::assertCount(1, $changed->releaseEvents());
        self::assertSame([], $r->find($this->id())?->releaseEvents());
    }

    private function register(FakeProfessionalRegistry $r): void
    {
        (new RegisterProfessional($r))->execute($this->id(), $this->registration(), ProfessionalName::fromString('Agence Horizon'), $this->now());
    }

    private function registerSecond(FakeProfessionalRegistry $r): void
    {
        (new RegisterProfessional($r))->execute($this->secondId(), RegistrationNumber::fromString('SN-NINEA/99999'), ProfessionalName::fromString('Seconde agence'), $this->now());
    }

    private function id(): ProfessionalId
    {
        return ProfessionalId::fromString('40000000-0000-4000-8000-000000000001');
    }

    private function registration(): RegistrationNumber
    {
        return RegistrationNumber::fromString('SN-NINEA/12345');
    }

    private function secondId(): ProfessionalId
    {
        return ProfessionalId::fromString('40000000-0000-4000-8000-000000000098');
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
}
