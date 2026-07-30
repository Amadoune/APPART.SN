<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaIngestionRuntimeArchitectureTest extends TestCase
{
    #[Test]
    public function runtime_application_is_framework_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/MediaIngestionRuntime';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['\\Infrastructure\\', 'Illuminate\\', 'PDO', 'SELECT ', 'Controller', 'Route', 'Event', 'Outbox', 'Delivery'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function owner_providers_are_additive_and_exclude_forbidden_surfaces(): void
    {
        $root = dirname(__DIR__, 2);
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        foreach (['MediaUpload', 'MediaAsset', 'MediaProcessing', 'MediaQuota', 'MediaIngestion'] as $owner) {
            $provider = $owner.'RuntimeServiceProvider';
            self::assertStringContainsString($provider.'::class', $providers);
            $source = (string) file_get_contents($root.'/app/Providers/'.$provider.'.php');
            foreach (['Controller', 'Route', 'Middleware', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
    }

    #[Test]
    public function frozen_runtime_health_remains_exactly_at_fifty_eight(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertStringNotContainsString('MediaIngestion', $requirements);
    }
}
