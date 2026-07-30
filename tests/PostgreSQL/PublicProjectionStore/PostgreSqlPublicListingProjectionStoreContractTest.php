<?php

namespace Tests\PostgreSQL\PublicProjectionStore;

use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\PublicProjectionStore\PublicListingProjectionStoreContract;
use Tests\Unit\Contracts\PublicProjectionStore\PublicListingProjectionStoreHarness;

final class PostgreSqlPublicListingProjectionStoreContractTest extends PublicListingProjectionStoreContract
{
    protected function createHarness(): PublicListingProjectionStoreHarness
    {
        return new PostgreSqlPublicListingProjectionStoreHarness(PostgreSqlTestEnvironment::connection());
    }
}
