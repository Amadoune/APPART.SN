<?php

namespace Tests\Unit\Modules\Professionals\Support;

use Appart\Modules\Professionals\Application\Contract\ProfessionalRegistry;
use Appart\Modules\Professionals\Domain\Exception\ConcurrentProfessionalModification;
use Appart\Modules\Professionals\Domain\Exception\EstablishmentIdConflict;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalIdConflict;
use Appart\Modules\Professionals\Domain\Exception\RegistrationNumberConflict;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;

final class FakeProfessionalRegistry implements ProfessionalRegistry
{
    /** @var array<string, Professional> */
    private array $professionals = [];

    /** @var array<string, string> establishment id => professional id */
    private array $establishmentOwners = [];

    private bool $failNextSave = false;

    public function find(ProfessionalId $id): ?Professional
    {
        return isset($this->professionals[$id->value]) ? clone $this->professionals[$id->value] : null;
    }

    public function add(Professional $professional): void
    {
        foreach ($this->professionals as $stored) {
            if ($stored->id()->equals($professional->id())) {
                throw new ProfessionalIdConflict;
            }
            if ($stored->registrationNumber()->equals($professional->registrationNumber())) {
                throw new RegistrationNumberConflict;
            }
        }
        $this->professionals[$professional->id()->value] = $this->cleanSnapshot($professional);
    }

    public function save(Professional $professional, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentProfessionalModification;
        }
        $stored = $this->professionals[$professional->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentProfessionalModification;
        }
        $this->professionals[$professional->id()->value] = $this->cleanSnapshot($professional);
    }

    public function saveWithEstablishmentReservation(Professional $professional, EstablishmentId $establishmentId, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentProfessionalModification;
        }

        $stored = $this->professionals[$professional->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentProfessionalModification;
        }

        $owner = $this->establishmentOwners[$establishmentId->value] ?? null;
        if ($owner !== null && $owner !== $professional->id()->value) {
            throw new EstablishmentIdConflict;
        }

        $this->professionals[$professional->id()->value] = $this->cleanSnapshot($professional);
        $this->establishmentOwners[$establishmentId->value] = $professional->id()->value;
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }

    private function cleanSnapshot(Professional $professional): Professional
    {
        $snapshot = clone $professional;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
