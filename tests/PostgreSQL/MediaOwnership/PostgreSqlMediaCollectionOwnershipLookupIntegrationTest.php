<?php

namespace Tests\PostgreSQL\MediaOwnership;

use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaCollectionOwnershipLookupIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_lookup_index_exists_on_the_official_property_ownership_column(): void
    {
        $statement = $this->connection->prepare("SELECT indexdef FROM pg_indexes WHERE schemaname='media' AND indexname='media_collections_property_idx'");
        $statement->execute();
        $definition = $statement->fetchColumn();

        self::assertIsString($definition);
        self::assertStringContainsString('(property_id)', $definition);
    }
}
