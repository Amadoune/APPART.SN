<?php

namespace Tests\PostgreSQL\RealEstateCatalog;

use Tests\Unit\Contracts\RealEstateCatalog\PropertyRegistryContract;
use Tests\Unit\Contracts\RealEstateCatalog\PropertyRegistryHarness;

final class PostgreSqlPropertyRegistryContractTest extends PropertyRegistryContract
{
    protected function createHarness(): PropertyRegistryHarness
    {
        return new PostgreSqlPropertyRegistryHarness;
    }
}
