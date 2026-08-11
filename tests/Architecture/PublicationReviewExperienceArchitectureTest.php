<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicationReviewExperienceArchitectureTest extends TestCase
{
    public function test_product_composition_uses_only_certified_contracts_and_no_sql_or_search(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/app/Application/PublicationReviewExperience/DeterministicPublicationReviewExperience.php');

        foreach ([
            'PublicationReviewAuthorizationReaderV1', 'PublicationReviewQueueReaderV1', 'ClaimPublicationReviewV1',
            'BeginPublicationReviewV1', 'ApprovePublicationV1', 'ProjectPublishedListingV1',
        ] as $contract) {
            self::assertStringContainsString($contract, $source);
        }
        foreach (['PDO', 'DB::', 'SELECT ', 'INSERT ', 'UPDATE ', 'PublicSearchResultsReader', 'PublicListingProjectionUpdater', 'ModeratorAuthorization'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_routes_are_separate_from_report_moderation(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        self::assertStringContainsString("Route::prefix('/publication-review')", $routes);
        self::assertStringContainsString('PublicationReviewExperienceController', $routes);
    }
}
