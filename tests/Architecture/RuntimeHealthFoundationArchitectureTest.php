<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class RuntimeHealthFoundationArchitectureTest extends TestCase
{
    public function test_health_foundation_is_application_only_and_side_effect_free(): void
    {
        $path = dirname(__DIR__, 2).'/app/Application/RuntimeHealth';
        foreach (glob($path.'/{,Contract/}*.php', GLOB_BRACE) ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', '\\Infrastructure\\', 'PDO', 'Http', 'CURRENT_TIMESTAMP', 'clock_timestamp', 'now()', 'readPage(', 'update(', 'rebuild(', 'write(', 'INSERT ', 'UPDATE ', 'DELETE '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_certified_requirements_name_every_required_runtime_capability(): void
    {
        $file = file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertIsString($file);
        foreach (['ProjectionSourceLookup', 'ProjectionRuntimeSource', 'PublicGeographySource', 'PublicMediaSource', 'MediaCollectionPropertyResolver', 'MultiTargetStrategy', 'ProjectionUpdater', 'ProjectionStore', 'RebuildEnumerator', 'DeliveryConsumer', 'HistoricalRedirectResolver', 'HistoricalCanonicalQualifier'] as $component) {
            self::assertStringContainsString('RuntimeHealthComponent::'.$component, $file);
        }
    }
}
