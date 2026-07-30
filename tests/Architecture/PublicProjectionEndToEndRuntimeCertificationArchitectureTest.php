<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicProjectionEndToEndRuntimeCertificationArchitectureTest extends TestCase
{
    public function test_e2e_campaign_never_constructs_runtime_components_or_uses_test_doubles(): void
    {
        $contents = file_get_contents(dirname(__DIR__).'/Feature/PublicProjectionEndToEndRuntimeCertificationTest.php');
        self::assertIsString($contents);
        foreach (['Fake', 'NullObject', 'new PostgreSqlListingRepository', 'new PostgreSqlAggregateOutboxTransaction', 'new PublicProjectionDeliveryWorker', 'new PublicProjectionUpdaterConsumer', 'new CertifiedPublicListingProjectionSource', 'new PostgreSqlPublicListingProjectionStore'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        foreach (['make(ListingRegistry::class)', 'make(PostgreSqlAggregateOutboxTransaction::class)', 'make(PublicProjectionDeliveryWorker::class)', 'make(PublicListingQuery::class)', 'make(RuntimeHealthInspector::class)'] as $required) {
            self::assertStringContainsString($required, $contents);
        }
    }
}
