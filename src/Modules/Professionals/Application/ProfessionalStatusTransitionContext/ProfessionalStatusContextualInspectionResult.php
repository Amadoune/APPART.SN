<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;

final readonly class ProfessionalStatusContextualInspectionResult
{
    private function __construct(
        public ProfessionalStatusId $professionalId,
        public ProfessionalStatusContextualInspectionStatus $status,
        public ?ProfessionalStatusContextualAppendInspection $snapshot,
    ) {}

    public static function found(ProfessionalStatusContextualAppendInspection $snapshot): self
    {
        return new self($snapshot->professionalId, ProfessionalStatusContextualInspectionStatus::Found, $snapshot);
    }

    public static function missing(ProfessionalStatusId $professionalId): self
    {
        return new self($professionalId, ProfessionalStatusContextualInspectionStatus::Missing, null);
    }

    public static function corrupted(ProfessionalStatusId $professionalId): self
    {
        return new self($professionalId, ProfessionalStatusContextualInspectionStatus::Corrupted, null);
    }
}
