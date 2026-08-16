<?php

namespace Tests\Unit\PostgreSql;

use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlTestEnvironmentIsolationTest extends TestCase
{
    public function test_distinct_application_and_test_databases_are_accepted(): void
    {
        PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated('appart_test', 'appart_rebuild');

        self::addToAssertionCount(1);
    }

    public function test_application_database_is_refused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('refuse to use the local application database');

        PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated('appart_test', 'appart_test');
    }

    public function test_non_test_database_name_is_refused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('explicitly test-only database name');

        PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated('appart_rebuild', 'appart_local');
    }

    public function test_missing_application_database_identity_is_refused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be declared');

        PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated('appart_test', '');
    }
}
