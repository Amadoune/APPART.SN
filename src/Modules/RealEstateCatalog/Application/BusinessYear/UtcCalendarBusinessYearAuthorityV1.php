<?php

namespace Appart\Modules\RealEstateCatalog\Application\BusinessYear;

use Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract\BusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use DateTimeZone;

final readonly class UtcCalendarBusinessYearAuthorityV1 implements BusinessYearAuthorityV1
{
    public function resolve(PropertyDecisionOccurredAt $occurredAt): BusinessYearResolutionResult
    {
        $utc = $occurredAt->instant()->setTimezone(new DateTimeZone('UTC'));

        return BusinessYearResolutionResult::resolved(BusinessYear::fromInt((int) $utc->format('Y')));
    }
}
