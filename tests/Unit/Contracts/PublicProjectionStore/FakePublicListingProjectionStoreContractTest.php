<?php

namespace Tests\Unit\Contracts\PublicProjectionStore;

final class FakePublicListingProjectionStoreContractTest extends PublicListingProjectionStoreContract
{
    protected function createHarness(): PublicListingProjectionStoreHarness
    {
        return new FakePublicListingProjectionStoreHarness;
    }
}
