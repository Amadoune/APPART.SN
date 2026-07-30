<?php

namespace Appart\Modules\Professionals\Application\Contract;

use Appart\Modules\Professionals\Domain\Exception\ConcurrentProfessionalModification;
use Appart\Modules\Professionals\Domain\Exception\EstablishmentIdConflict;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalIdConflict;
use Appart\Modules\Professionals\Domain\Exception\RegistrationNumberConflict;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;

interface ProfessionalRegistry
{
    /** Returns a detached Aggregate without previously released or persisted events. */
    public function find(ProfessionalId $id): ?Professional;

    /**
     * Atomically adds a unique ProfessionalId and RegistrationNumber.
     *
     * @throws ProfessionalIdConflict
     * @throws RegistrationNumberConflict
     */
    public function add(Professional $professional): void;

    /**
     * Saves a clean snapshot only when the stored version equals expectedVersion.
     * Domain events remain on the caller's Aggregate until releaseEvents().
     *
     * @throws ConcurrentProfessionalModification
     */
    public function save(Professional $professional, int $expectedVersion): void;

    /**
     * Atomically reserves EstablishmentId forever for this Professional and conditionally saves it.
     * Neither the reservation nor the mutation becomes visible when the operation fails.
     *
     * @throws EstablishmentIdConflict
     * @throws ConcurrentProfessionalModification
     */
    public function saveWithEstablishmentReservation(Professional $professional, EstablishmentId $establishmentId, int $expectedVersion): void;
}
