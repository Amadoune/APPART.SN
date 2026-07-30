<?php

namespace Tests\Unit\Contracts\ListingLifecycle;

final class FakeListingRegistryContractTest extends ListingRegistryContract
{
    protected function createHarness(): ListingRegistryHarness
    {
        return new FakeListingRegistryHarness;
    }
}
