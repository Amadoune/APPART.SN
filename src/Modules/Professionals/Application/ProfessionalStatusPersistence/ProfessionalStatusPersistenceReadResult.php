<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPersistence;

final readonly class ProfessionalStatusPersistenceReadResult
{
    private function __construct(public ProfessionalStatusId $professionalId, public ProfessionalStatusPersistenceReadStatus $status, public ?ProfessionalStatusStoredState $snapshot) {}

    public static function found(ProfessionalStatusStoredState $snapshot): self
    {
        return new self($snapshot->professionalId, ProfessionalStatusPersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(ProfessionalStatusId $professionalId): self
    {
        return new self($professionalId, ProfessionalStatusPersistenceReadStatus::Missing, null);
    }

    public static function corrupted(ProfessionalStatusId $professionalId): self
    {
        return new self($professionalId, ProfessionalStatusPersistenceReadStatus::Corrupted, null);
    }
}
