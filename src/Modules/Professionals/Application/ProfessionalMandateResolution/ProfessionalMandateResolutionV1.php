<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateResolution;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use LogicException;

final readonly class ProfessionalMandateResolutionV1
{
    private function __construct(
        public ProfessionalMandateResolutionStatusV1 $status,
        public ?ProfessionalId $professionalId = null,
    ) {
        if (($status === ProfessionalMandateResolutionStatusV1::Resolved) !== ($professionalId !== null)) {
            throw new LogicException('Only a resolved mandate may expose a ProfessionalId.');
        }
    }

    public static function resolved(ProfessionalId $professionalId): self
    {
        return new self(ProfessionalMandateResolutionStatusV1::Resolved, $professionalId);
    }

    public static function notMandated(): self
    {
        return new self(ProfessionalMandateResolutionStatusV1::NotMandated);
    }

    public static function ambiguous(): self
    {
        return new self(ProfessionalMandateResolutionStatusV1::Ambiguous);
    }

    public static function corrupted(): self
    {
        return new self(ProfessionalMandateResolutionStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ProfessionalMandateResolutionStatusV1::DependencyUnavailable);
    }
}
