<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class OwnerDashboardReadExperienceArchitectureTest extends TestCase
{
    public function test_controller_depends_only_on_the_injectable_dashboard_read_source(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/OwnerDashboardController.php');

        self::assertStringContainsString('OwnerDashboardReadSourceV1', $controller);
        foreach (['PDO', 'DB::', 'PostgreSql', 'IdentityAccessHttpRuntime', 'PropertyListingAuthoring'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_dashboard_adapter_reads_only_existing_public_contracts(): void
    {
        $adapter = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/OwnerDashboard/PublicProjectionOwnerDashboardReadSource.php');

        self::assertStringContainsString('PublicSearchResultsReaderV1', $adapter);
        self::assertStringContainsString('PublicListingQuery', $adapter);
        foreach (['PDO', 'DB::', 'PostgreSql', 'AccountRegistry', 'ListingRegistry'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $adapter);
        }
    }
}
