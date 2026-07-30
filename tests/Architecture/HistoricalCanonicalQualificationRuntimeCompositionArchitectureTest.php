<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class HistoricalCanonicalQualificationRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_alias_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlHistoricalCanonicalQualifier::class, HistoricalCanonicalQualifier::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlHistoricalCanonicalQualifier::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(HistoricalCanonicalQualificationMapper::class)'));
        self::assertSame([], glob($root.'/app/Providers/*HistoricalCanonical*') ?: []);
    }

    public function test_bootstrap_does_not_qualify_construct_identity_or_query_postgresql(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringNotContainsString('->qualify(', $provider);
        self::assertStringNotContainsString('HistoricalCanonical::', $provider);
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_only_inspects_the_qualification_contract(): void
    {
        $requirements = file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertIsString($requirements);
        self::assertStringContainsString('RuntimeHealthComponent::HistoricalCanonicalQualifier, HistoricalCanonicalQualifier::class', $requirements);
        foreach (['PostgreSqlHistoricalCanonicalQualifier', 'HistoricalCanonicalQualificationMapper', 'CanonicalUrl', 'qualify('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_http_uses_only_the_certified_qualification_port(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (array_merge(glob($root.'/app/Http/**/*.php') ?: [], glob($root.'/routes/*.php') ?: []) as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertStringNotContainsString('PostgreSqlHistoricalCanonicalQualifier', $contents, $file);
            self::assertStringNotContainsString('HistoricalCanonicalQualificationMapper', $contents, $file);
            self::assertStringNotContainsString('HistoricalCanonical::declared', $contents, $file);
        }
    }
}
