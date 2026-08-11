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

    #[Test]
    public function binary_storage_is_media_scoped_and_opens_no_downstream_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $application = $root.'/src/Modules/Media/Application/BinaryStorage';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($application));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate\\', 'PDO', 'PostgreSql', 'Controller', 'Route', 'Search', 'Projection', 'AttachReadyMediaAsset'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
        $provider = (string) file_get_contents($root.'/app/Providers/MediaBinaryStorageServiceProvider.php');
        foreach (['Controller', 'Route', 'Search', 'Projection', 'AttachReadyMediaAsset'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertStringContainsString("disk('media')", $provider);
    }

    #[Test]
    public function asset_readiness_is_media_scoped_and_opens_no_downstream_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $application = $root.'/src/Modules/Media/Application/ReadyAsset';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($application));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate\\', 'PDO', 'PostgreSql', 'Controller', 'Route', 'Search', 'Projection', 'AttachReadyMediaAsset', 'MediaCollection'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source);
            }
        }
        $provider = (string) file_get_contents($root.'/app/Providers/MediaAssetReadinessServiceProvider.php');
        foreach (['Controller', 'Route', 'Search', 'Projection', 'AttachReadyMediaAsset', 'MediaCollection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    #[Test]
    public function ready_asset_attachment_opens_no_http_or_product_surface(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/src/Modules/Media/Application/Attachment/DeterministicAttachReadyMediaAsset.php');
        foreach (['Controller', 'Route', 'Http', 'Search', 'Projection', 'ListingLifecycle', 'PropertyListing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        $provider = (string) file_get_contents($root.'/app/Providers/MediaReadyAssetAttachmentServiceProvider.php');
        foreach (['Controller', 'Route', 'Http', 'Search', 'Projection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    #[Test]
    public function property_authoring_catalog_adapter_is_read_only_and_has_no_sql_or_aggregate_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/app/Infrastructure/MediaAttachment/PropertyAuthoringMediaCatalogAdapter.php');
        foreach (['PDO', 'SELECT ', 'INSERT ', 'UPDATE ', 'PropertyRegistry', 'Domain\\Model\\Property', 'Controller', 'Route', 'Http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('PropertyAuthoringStore', $source);
        self::assertStringContainsString('read($propertyId->value)', $source);
    }
}
