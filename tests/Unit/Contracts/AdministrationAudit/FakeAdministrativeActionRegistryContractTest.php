<?php

namespace Tests\Unit\Contracts\AdministrationAudit;

final class FakeAdministrativeActionRegistryContractTest extends AdministrativeActionRegistryContract
{
    protected function createHarness(): AdministrativeActionRegistryHarness
    {
        return new FakeAdministrativeActionRegistryHarness;
    }
}
