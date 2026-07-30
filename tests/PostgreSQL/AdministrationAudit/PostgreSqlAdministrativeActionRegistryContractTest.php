<?php

namespace Tests\PostgreSQL\AdministrationAudit;

use Tests\Unit\Contracts\AdministrationAudit\AdministrativeActionRegistryContract;
use Tests\Unit\Contracts\AdministrationAudit\AdministrativeActionRegistryHarness;

final class PostgreSqlAdministrativeActionRegistryContractTest extends AdministrativeActionRegistryContract
{
    protected function createHarness(): AdministrativeActionRegistryHarness
    {
        return new PostgreSqlAdministrativeActionRegistryHarness;
    }
}
