<?php

namespace Tests\Unit\Contracts\Media;

final class FakeMediaCollectionRegistryContractTest extends MediaCollectionRegistryContract
{
    protected function createHarness(): MediaCollectionRegistryHarness
    {
        return new FakeMediaCollectionRegistryHarness;
    }
}
