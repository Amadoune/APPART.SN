<?php

namespace Tests\Architecture;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListingPublicationWorkflowPostgreSqlArchitectureTest extends TestCase
{
    public function test_repository_implements_exactly_the_persistence_contract(): void
    {
        $repository = new ReflectionClass(PostgreSqlListingPublicationWorkflowRepository::class);
        self::assertTrue($repository->isFinal());
        self::assertTrue($repository->isReadOnly());
        self::assertSame([ListingPublicationWorkflowStore::class], $repository->getInterfaceNames());
    }

    public function test_persistence_has_no_runtime_http_laravel_event_or_aggregate_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence';
        foreach ([$root.'/ListingPublicationWorkflowMapper.php', $root.'/PostgreSql/PostgreSqlListingPublicationWorkflowRepository.php', $root.'/PostgreSql/Migrations/015_listing_publication_workflow.sql'] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'Http', '\\Application\\Runtime', 'Projection', 'Outbox', 'dispatch', 'event(', 'Listing::', 'ListingRegistry', 'HistoricalRedirect', 'PublicListingQuery'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_adapter_persists_provided_transition_without_workflow_recalculation(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/PostgreSqlListingPublicationWorkflowRepository.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringNotContainsString('use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;', $contents);
        self::assertStringNotContainsString('->decide(', $contents);
        self::assertStringNotContainsString('match (', $contents);
        self::assertStringContainsString('$this->mapper->transition($listingId, $transition, $version)', $contents);
    }

    public function test_migration_encodes_all_twenty_seven_constraints_without_decision_columns(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/015_listing_publication_workflow.sql');
        self::assertIsString($contents);
        self::assertSame(27, preg_match_all("/\\('[a-z_]+','[a-z_]+','[a-z_]+'\\)/", $contents));
        self::assertStringContainsString('version bigint NOT NULL CHECK (version > 0)', $contents);
        self::assertStringContainsString('PRIMARY KEY (listing_id, version)', $contents);
        self::assertStringContainsString('publication_workflow_current_state_lookup', $contents);
        foreach (['decision_status', 'diagnostic_code', 'allowed boolean'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
