<?php

namespace Tests\PostgreSQL\Media;

use Tests\Unit\Contracts\Media\MediaCollectionRegistryContract;
use Tests\Unit\Contracts\Media\MediaCollectionRegistryHarness;

final class PostgreSqlMediaCollectionRegistryContractTest extends MediaCollectionRegistryContract
{
    protected function createHarness(): MediaCollectionRegistryHarness
    {
        return new PostgreSqlMediaCollectionRegistryHarness;
    }
}
