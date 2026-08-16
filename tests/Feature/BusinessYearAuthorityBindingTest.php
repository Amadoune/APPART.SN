<?php

namespace Tests\Feature;

use Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract\BusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Tests\TestCase;

final class BusinessYearAuthorityBindingTest extends TestCase
{
    public function test_business_year_authority_resolves_to_the_utc_calendar_implementation(): void
    {
        self::assertInstanceOf(UtcCalendarBusinessYearAuthorityV1::class, $this->app->make(BusinessYearAuthorityV1::class));
    }
}
