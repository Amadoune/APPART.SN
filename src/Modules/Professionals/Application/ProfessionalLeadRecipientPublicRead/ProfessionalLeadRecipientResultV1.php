<?php

namespace Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead;

final readonly class ProfessionalLeadRecipientResultV1
{
    private function __construct(public ProfessionalLeadRecipientStatusV1 $status) {}

    public static function eligible(): self
    {
        return new self(ProfessionalLeadRecipientStatusV1::Eligible);
    }

    public static function notEligible(): self
    {
        return new self(ProfessionalLeadRecipientStatusV1::NotEligible);
    }

    public static function missing(): self
    {
        return new self(ProfessionalLeadRecipientStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ProfessionalLeadRecipientStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ProfessionalLeadRecipientStatusV1::DependencyUnavailable);
    }
}
