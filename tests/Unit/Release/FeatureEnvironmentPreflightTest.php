<?php

namespace Tests\Unit\Release;

use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\FeatureEnvironmentPreflight;
use Tests\TestCase;

final class FeatureEnvironmentPreflightTest extends TestCase
{
    public function test_qualified_feature_environment_is_accepted(): void
    {
        self::assertSame('appart_test', FeatureEnvironmentPreflight::verify($this->environment(), fn (): string => 'appart_test'));
    }

    #[DataProvider('missingVariables')]
    public function test_required_variables_fail_closed_without_exposing_values(string $name): void
    {
        $environment = $this->environment();
        $environment[$name] = '';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Feature environment variable {$name} is required.");
        FeatureEnvironmentPreflight::verify($environment, fn (): string => 'appart_test');
    }

    public function test_root_fallback_is_refused(): void
    {
        $environment = $this->environment();
        $environment['DB_USERNAME'] = 'root';

        $this->expectExceptionMessage('cannot use the root fallback');
        FeatureEnvironmentPreflight::verify($environment, fn (): string => 'appart_test');
    }

    public function test_unexpected_database_is_refused(): void
    {
        $this->expectExceptionMessage('must be appart_test');
        FeatureEnvironmentPreflight::verify($this->environment(), fn (): string => 'appart_rebuild');
    }

    public function test_database_collision_is_refused_by_existing_guard(): void
    {
        $environment = $this->environment();
        $environment['APPART_APPLICATION_PG_DATABASE'] = 'appart_test';

        $this->expectExceptionMessage('refuse to use the local application database');
        FeatureEnvironmentPreflight::verify($environment, fn (): string => 'appart_test');
    }

    public static function missingVariables(): iterable
    {
        foreach (['APP_KEY', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $name) {
            yield $name => [$name];
        }
    }

    /** @return array<string, string> */
    private function environment(): array
    {
        return [
            'APP_ENV' => 'testing', 'APP_KEY' => 'external-test-key', 'DB_CONNECTION' => 'pgsql',
            'DB_HOST' => '127.0.0.1', 'DB_PORT' => '5432', 'DB_DATABASE' => 'appart_test',
            'DB_USERNAME' => 'appart_test', 'DB_PASSWORD' => 'external-password',
            'APPART_APPLICATION_PG_DATABASE' => 'appart_rebuild',
        ];
    }
}
