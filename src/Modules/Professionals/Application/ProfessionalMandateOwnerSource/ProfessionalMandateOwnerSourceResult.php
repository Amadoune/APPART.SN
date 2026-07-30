<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use LogicException;

final readonly class ProfessionalMandateOwnerSourceResult
{
    private function __construct(
        public ProfessionalMandateOwnerSourceStatus $status,
        public ?ProfessionalId $professionalId = null,
    ) {
        if (($status === ProfessionalMandateOwnerSourceStatus::Resolved) !== ($professionalId !== null)) {
            throw new LogicException('Only a resolved owner source may expose a ProfessionalId.');
        }
    }

    public static function resolved(ProfessionalId $professionalId): self
    {
        return new self(ProfessionalMandateOwnerSourceStatus::Resolved, $professionalId);
    }

    public static function notMandated(): self
    {
        return new self(ProfessionalMandateOwnerSourceStatus::NotMandated);
    }

    public static function ambiguous(): self
    {
        return new self(ProfessionalMandateOwnerSourceStatus::Ambiguous);
    }

    public static function corrupted(): self
    {
        return new self(ProfessionalMandateOwnerSourceStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ProfessionalMandateOwnerSourceStatus::DependencyUnavailable);
    }
}
