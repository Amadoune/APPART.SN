<?php

namespace Tests\Support;

use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class FeatureEnvironmentPreflight
{
    /** @param array<string, string|false> $environment */
    public static function verify(array $environment, callable $currentDatabase): string
    {
        foreach (['APP_ENV', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APPART_APPLICATION_PG_DATABASE'] as $name) {
            if (! is_string($environment[$name] ?? false) || $environment[$name] === '') {
                throw new RuntimeException("Feature environment variable {$name} is required.");
            }
        }

        if ($environment['APP_ENV'] !== 'testing' || $environment['DB_CONNECTION'] !== 'pgsql') {
            throw new RuntimeException('Feature environment must use testing with PostgreSQL.');
        }
        if ($environment['DB_USERNAME'] === 'root') {
            throw new RuntimeException('Feature database user must be explicit and cannot use the root fallback.');
        }

        $database = $currentDatabase();
        if (! is_string($database) || $database !== 'appart_test' || $environment['DB_DATABASE'] !== $database) {
            throw new RuntimeException('Feature database identity must be appart_test.');
        }

        PostgreSqlTestEnvironment::assertDatabaseNamesAreIsolated($database, $environment['APPART_APPLICATION_PG_DATABASE']);

        return $database;
    }
}
