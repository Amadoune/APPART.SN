<?php

namespace Tests\PostgreSQL\LeadEligibilitySourceData;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\LeadEligibilitySourceData\LeadEligibilityDecisionMaterializerContract;

final class PostgreSqlLeadEligibilityDecisionStoreContractTest extends LeadEligibilityDecisionMaterializerContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function materializer(): LeadEligibilityDecisionMaterializer
    {
        return $this->store();
    }

    protected function reader(): LeadEligibilitySourceDataReader
    {
        return $this->store();
    }

    private function store(): PostgreSqlLeadEligibilityDecisionStore
    {
        return new PostgreSqlLeadEligibilityDecisionStore($this->connection, new LeadEligibilitySourceDataMapper);
    }
}
