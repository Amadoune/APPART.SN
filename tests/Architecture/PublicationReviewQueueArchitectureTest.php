<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PublicationReviewQueueArchitectureTest extends TestCase
{
    public function test_application_queue_has_no_framework_or_persistence_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/PublicationReview/Application/Queue/**/*.php') ?: [];
        $files = array_merge($files, glob($root.'/src/Modules/PublicationReview/Application/Queue/*.php') ?: []);
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Controller', 'Search', 'PublicProjectionUpdater', 'BeginReview', 'ApprovePublication'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_persistence_contains_required_concurrency_guarantees(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/PostgreSqlPublicationReviewQueue.php');
        foreach (['pg_advisory_xact_lock', 'FOR UPDATE', 'SAVEPOINT', 'ROLLBACK TO SAVEPOINT', 'expectedVersion'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        self::assertFileExists($root.'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/Migrations/096_publication_review_queue.sql');
        self::assertFileExists($root.'/src/Modules/PublicationReview/Infrastructure/Persistence/PostgreSql/Migrations/096_publication_review_queue.down.sql');
    }

    public function test_listing_event_integration_hands_events_to_the_independent_consumer(): void
    {
        $integration = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/ListingPublicationEventIntegration/AtomicListingPublicationEventOrchestrator.php');
        self::assertStringContainsString('PublicationReviewConsumer', $integration);
        self::assertStringContainsString('publicationReview->consume($event)', $integration);
    }

    public function test_review_commands_depend_only_on_queue_and_listing_gateway_contracts(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/PublicationReview/Application/Review/**/*.php') ?: [];
        $files = array_merge($files, glob($root.'/src/Modules/PublicationReview/Application/Review/*.php') ?: []);
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Property', 'Media', 'Projection', 'Search', 'ListingRegistry', 'Repository'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
        $commands = (string) file_get_contents($root.'/src/Modules/PublicationReview/Application/Review/DeterministicPublicationReviewCommands.php');
        self::assertStringContainsString('ListingPublicationCommandGatewayV1', $commands);
    }

    public function test_projection_intention_has_no_search_sql_or_projection_implementation_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/PublicationReview/Application/Projection/**/*.php') ?: [];
        $files = array_merge($files, glob($root.'/src/Modules/PublicationReview/Application/Projection/*.php') ?: []);
        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PDO', 'PostgreSql', 'Infrastructure\\', 'Illuminate\\', 'Search', 'PublicListingProjectionUpdater', 'SELECT ', 'INSERT ', 'UPDATE '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
        $adapter = (string) file_get_contents($root.'/app/Application/PublicationReviewProjectionActivation/PublicListingProjectionActivationAdapter.php');
        self::assertStringContainsString('PublicListingProjectionUpdater', $adapter);
        self::assertStringNotContainsString('Search', $adapter);
    }
}
