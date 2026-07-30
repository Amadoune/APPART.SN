<?php

namespace Tests\Unit\Contracts\RealEstateCatalog;

final class FakePropertyRegistryContractTest extends PropertyRegistryContract
{
    protected function createHarness(): PropertyRegistryHarness
    {
        return new FakePropertyRegistryHarness;
    }
}
