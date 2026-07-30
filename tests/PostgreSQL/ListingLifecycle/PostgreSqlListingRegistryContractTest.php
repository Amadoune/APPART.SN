<?php

namespace Tests\PostgreSQL\ListingLifecycle;

use Tests\Unit\Contracts\ListingLifecycle\ListingRegistryContract;
use Tests\Unit\Contracts\ListingLifecycle\ListingRegistryHarness;

final class PostgreSqlListingRegistryContractTest extends ListingRegistryContract
{
    protected function createHarness(): ListingRegistryHarness
    {
        return new PostgreSqlListingRegistryHarness;
    }
}
