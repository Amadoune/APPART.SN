<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class HistoricalRedirectRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_composition_is_limited_to_the_existing_runtime_root(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlHistoricalRedirectResolver::class, HistoricalRedirectResolver::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlHistoricalRedirectResolver::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(HistoricalRedirectDecisionMapper::class)'));

        foreach (['app/Http', 'routes'] as $directory) {
            foreach (glob($root.'/'.$directory.'/*.php') ?: [] as $file) {
                $contents = file_get_contents($file);
                self::assertIsString($contents);
                self::assertStringNotContainsString('PostgreSqlHistoricalRedirectResolver', $contents, $file);
                self::assertStringNotContainsString('HistoricalRedirectDecisionMapper', $contents, $file);
                self::assertStringNotContainsString('HistoricalCanonical::declared', $contents, $file);
            }
        }
    }

    public function test_bootstrap_registers_without_resolving_or_querying_historical_redirects(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringNotContainsString('->resolve(', $provider);
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
        self::assertStringNotContainsString('HistoricalCanonical::', $provider);
        self::assertStringNotContainsString('CanonicalUrl', $provider);
    }

    public function test_runtime_health_only_inspects_the_contract_registration(): void
    {
        $requirements = file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertIsString($requirements);
        self::assertStringContainsString('RuntimeHealthComponent::HistoricalRedirectResolver, HistoricalRedirectResolver::class', $requirements);
        foreach (['PostgreSqlHistoricalRedirectResolver', 'HistoricalRedirectDecisionMapper', 'HistoricalCanonical::', 'resolve('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_no_production_fake_null_object_or_second_provider_exists(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (glob($root.'/{app,src}/**/*HistoricalRedirect*.php', GLOB_BRACE) ?: [] as $file) {
            self::assertDoesNotMatchRegularExpression('/(?:Fake|NullHistorical)/', basename($file), $file);
        }
        self::assertSame([], glob($root.'/app/Providers/*HistoricalRedirect*') ?: []);
    }
}
