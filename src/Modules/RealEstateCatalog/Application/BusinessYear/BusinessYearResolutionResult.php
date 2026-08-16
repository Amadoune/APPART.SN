<?php

namespace Appart\Modules\RealEstateCatalog\Application\BusinessYear;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;

final readonly class BusinessYearResolutionResult
{
    private function __construct(public BusinessYearResolutionStatus $status, public BusinessYear $businessYear) {}

    public static function resolved(BusinessYear $businessYear): self
    {
        return new self(BusinessYearResolutionStatus::Resolved, $businessYear);
    }
}
