<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicProjectionRuntimeBindingArchitectureTest extends TestCase
{
    public function test_provider_only_contains_explicit_composition(): void
    {
        $file = dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        foreach (['Fake', 'Null', 'fallback', 'Http', 'Route::', 'route(', 'Request', 'Response', 'update(', 'rebuild(', 'readPage(', 'resolve(', 'SELECT ', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_provider_is_registered_once(): void
    {
        $providers = file_get_contents(dirname(__DIR__, 2).'/bootstrap/providers.php');
        self::assertIsString($providers);
        self::assertSame(2, substr_count($providers, 'PublicProjectionRuntimeServiceProvider'));
    }

    public function test_production_delivery_clock_has_no_framework_database_or_test_dependency(): void
    {
        $file = dirname(__DIR__, 2).'/app/Infrastructure/PublicProjectionWorker/SystemPublicProjectionDeliveryClock.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('implements PublicProjectionDeliveryClock', $contents);
        foreach (['Illuminate', 'PDO', 'Tests\\', 'Fake', 'Carbon', 'env(', 'config('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
