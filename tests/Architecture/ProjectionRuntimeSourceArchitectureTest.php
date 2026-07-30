<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProjectionRuntimeSourceArchitectureTest extends TestCase
{
    public function test_application_inspection_contract_has_no_infrastructure_or_framework_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ProjectionRuntimeSource';
        $files = [...(glob($root.'/*.php') ?: []), ...(glob($root.'/Contract/*.php') ?: [])];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Infrastructure|\bPDO\b|Http)/i', $contents);
        }
    }

    public function test_production_source_only_assembles_certified_ports(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/ProjectionRuntimeSource/CertifiedPublicListingProjectionSource.php');
        self::assertIsString($contents);
        foreach (['ListingRegistry', 'PropertyRegistry', 'MediaCollectionOwnershipLookup', 'MediaCollectionRegistry', 'SearchDecisionReader', 'ContentSeoSourceSnapshotReader', 'PublicGeographyDecisionReader', 'PublicMediaDecisionReader', 'ActiveGenerationReader', 'DecisionTimeReader'] as $port) {
            self::assertStringContainsString($port, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/(?:\bnow\s*\(|CURRENT_TIMESTAMP|clock_timestamp|\bPDO\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|SearchListingProjectionBuilder|ListingSeoDecisionPolicy|CanonicalPolicy)/i', $contents);
    }

    public function test_source_does_not_depend_on_laravel_http_runtime_or_delivery(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/ProjectionRuntimeSource/CertifiedPublicListingProjectionSource.php');
        self::assertIsString($contents);
        self::assertDoesNotMatchRegularExpression('/(?:Illuminate|Laravel|Controller|Route|Request|Response|Worker|Outbox|Dispatcher)/i', $contents);
    }
}
