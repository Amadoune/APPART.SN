<?php

namespace Appart\Modules\Professionals\Domain\Model;

use Appart\Modules\Professionals\Domain\Event\AbstractProfessionalEvent;
use Appart\Modules\Professionals\Domain\Event\EstablishmentAdded;
use Appart\Modules\Professionals\Domain\Event\EstablishmentRemoved;
use Appart\Modules\Professionals\Domain\Event\MandateGranted;
use Appart\Modules\Professionals\Domain\Event\MandateRevoked;
use Appart\Modules\Professionals\Domain\Event\ProfessionalEvent;
use Appart\Modules\Professionals\Domain\Event\ProfessionalReactivated;
use Appart\Modules\Professionals\Domain\Event\ProfessionalRegistered;
use Appart\Modules\Professionals\Domain\Event\ProfessionalSuspended;
use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalViolation;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentName;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalName;
use Appart\Modules\Professionals\Domain\ValueObject\RegistrationNumber;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use DateTimeImmutable;

final class Professional
{
    /** @var array<string, Establishment> */
    private array $establishments = [];

    /** @var array<string, RepresentativeMandate> */
    private array $mandates = [];

    /** @var list<ProfessionalEvent> */
    private array $events = [];

    private bool $suspended = false;

    private int $version = 0;

    private function __construct(private readonly ProfessionalId $id, private readonly RegistrationNumber $registrationNumber, private readonly ProfessionalName $name, private DateTimeImmutable $lastChangedAt) {}

    public static function register(ProfessionalId $id, RegistrationNumber $registrationNumber, ProfessionalName $name, DateTimeImmutable $at): self
    {
        $professional = new self($id, $registrationNumber, $name, $at);
        $professional->recordEvent(new ProfessionalRegistered($id, $name, $registrationNumber, $at), 0);

        return $professional;
    }

    /**
     * @param  list<Establishment>  $establishments
     * @param  list<RepresentativeMandate>  $mandates
     */
    public static function reconstitute(ProfessionalId $id, RegistrationNumber $registrationNumber, ProfessionalName $name, DateTimeImmutable $lastChangedAt, array $establishments, array $mandates, bool $suspended, int $version): self
    {
        if ($version < 0) {
            throw InvalidProfessionalValue::field('version');
        }
        $professional = new self($id, $registrationNumber, $name, $lastChangedAt);
        foreach ($establishments as $establishment) {
            $professional->establishments[$establishment->id->value] = $establishment;
        }
        foreach ($mandates as $mandate) {
            $professional->mandates[$mandate->id->value] = $mandate;
        }
        $professional->suspended = $suspended;
        $professional->version = $version;

        return $professional;
    }

    public function addEstablishment(EstablishmentId $id, EstablishmentName $name, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        if (isset($this->establishments[$id->value])) {
            throw ProfessionalViolation::establishmentAlreadyExists();
        }
        $this->establishments[$id->value] = new Establishment($id, $this->id, $name, $at);
        $this->recordEvent(new EstablishmentAdded($this->id, $id, $name, $at));
        $this->changed($at);
    }

    public function removeEstablishment(EstablishmentId $id, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $establishment = $this->establishments[$id->value] ?? throw ProfessionalViolation::establishmentNotFound();
        if (! $establishment->isActive()) {
            throw ProfessionalViolation::establishmentAlreadyRemoved();
        }
        foreach ($this->mandates as $mandate) {
            if ($mandate->isActive() && $mandate->establishmentId->equals($id)) {
                throw ProfessionalViolation::establishmentHasActiveMandates();
            }
        }
        $this->establishments[$id->value] = $establishment->remove($at);
        $this->recordEvent(new EstablishmentRemoved($this->id, $id, $at));
        $this->changed($at);
    }

    public function grantMandate(MandateId $id, EstablishmentId $establishmentId, RepresentativeId $representativeId, MandateRole $role, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        if ($this->suspended) {
            throw ProfessionalViolation::suspendedProfessional();
        }
        if (isset($this->mandates[$id->value])) {
            throw ProfessionalViolation::duplicateActiveMandate();
        }
        $establishment = $this->establishments[$establishmentId->value] ?? throw ProfessionalViolation::establishmentNotFound();
        if (! $establishment->isActive()) {
            throw ProfessionalViolation::establishmentAlreadyRemoved();
        }
        foreach ($this->mandates as $mandate) {
            if ($mandate->isActive() && $mandate->establishmentId->equals($establishmentId) && $mandate->representativeId->equals($representativeId)) {
                throw ProfessionalViolation::duplicateActiveMandate();
            }
        }
        $this->mandates[$id->value] = new RepresentativeMandate($id, $establishmentId, $representativeId, $role, $at);
        $this->recordEvent(new MandateGranted($this->id, $id, $establishmentId, $representativeId, $role, $at));
        $this->changed($at);
    }

    public function revokeMandate(MandateId $id, DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        $mandate = $this->mandates[$id->value] ?? throw ProfessionalViolation::mandateNotFound();
        if (! $mandate->isActive()) {
            throw ProfessionalViolation::mandateAlreadyRevoked();
        }
        $this->mandates[$id->value] = $mandate->revoke($at);
        $this->recordEvent(new MandateRevoked($this->id, $id, $at));
        $this->changed($at);
    }

    public function suspend(DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        if ($this->suspended) {
            throw ProfessionalViolation::alreadySuspended();
        }
        $this->suspended = true;
        $this->recordEvent(new ProfessionalSuspended($this->id, $at));
        $this->changed($at);
    }

    public function reactivate(DateTimeImmutable $at): void
    {
        $this->guardTime($at);
        if (! $this->suspended) {
            throw ProfessionalViolation::notSuspended();
        }
        $this->suspended = false;
        $this->recordEvent(new ProfessionalReactivated($this->id, $at));
        $this->changed($at);
    }

    public function id(): ProfessionalId
    {
        return $this->id;
    }

    public function registrationNumber(): RegistrationNumber
    {
        return $this->registrationNumber;
    }

    public function name(): ProfessionalName
    {
        return $this->name;
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function version(): int
    {
        return $this->version;
    }

    /** @return list<Establishment> */
    public function establishments(): array
    {
        return array_values($this->establishments);
    }

    /** @return list<RepresentativeMandate> */
    public function mandates(): array
    {
        return array_values($this->mandates);
    }

    /**
     * Releases newly produced events exactly once from this detached instance.
     * Registries must never include this transient collection in a stored snapshot.
     *
     * @return list<ProfessionalEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function guardTime(DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw InvalidProfessionalValue::field('event_time');
        }
    }

    private function changed(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    private function recordEvent(ProfessionalEvent $event, ?int $resultVersion = null): void
    {
        if ($event instanceof AbstractProfessionalEvent) {
            $event->stamp($resultVersion ?? $this->version + 1, count($this->events) + 1);
        }
        $this->events[] = $event;
    }
}
