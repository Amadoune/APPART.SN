<?php

namespace Tests\PostgreSQL\AuthoringOperations;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\DeterministicPropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeV1;
use App\Infrastructure\Authoring\PropertyAuthoringCatalogAdapter;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\AuthoringPortfolioMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingDraftMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingOwnershipMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAuthoringOperationsTest extends TestCase
{
    private const string ACCOUNT = '65000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '65000000-0000-4000-8000-000000000002';

    private const string LISTING = '65000000-0000-4000-8000-000000000003';

    private const string REVISION = '65000000-0000-4000-8000-000000000004';

    private const string PROPERTY_INTENT = '65000000-0000-4000-8000-000000000005';

    private const string LISTING_INTENT = '65000000-0000-4000-8000-000000000006';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_complete_listing_creation_is_atomic_replayable_and_owner_scoped(): void
    {
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);

        $command = $this->listingCommand();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($command)->status);
        self::assertSame(AuthoringOperationStatus::AlreadyApplied, $operations->execute($command)->status);

        self::assertSame(1, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.listing_creation_intents'));
        self::assertSame(1, $this->tableCount('listing_authoring.drafts'));
        self::assertSame(1, $this->tableCount('listing_authoring.ownerships'));
        self::assertSame(1, $this->tableCount('listing_authoring.portfolio_items'));
    }

    public function test_enclosing_transaction_rolls_back_listing_draft_ownership_and_portfolio(): void
    {
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);

        $this->connection->beginTransaction();
        try {
            self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);
            self::assertTrue($this->connection->inTransaction());
            self::assertSame(1, $this->tableCount('listing_authoring.drafts'));
        } finally {
            $this->connection->rollBack();
        }

        self::assertSame(0, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(0, $this->tableCount('listing_authoring.drafts'));
        self::assertSame(0, $this->tableCount('listing_authoring.ownerships'));
        self::assertSame(0, $this->tableCount('listing_authoring.portfolio_items'));
    }

    private function operations(): DeterministicPropertyListingAuthoringOperations
    {
        $propertyStore = new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper);
        $draftStore = new PostgreSqlListingDraftStore($this->connection, new ListingDraftMapper);
        $ownershipStore = new PostgreSqlListingOwnershipStore($this->connection, new ListingOwnershipMapper);
        $portfolioStore = new PostgreSqlAuthoringPortfolioStore($this->connection, new AuthoringPortfolioMapper);
        $runtime = new DeterministicPropertyListingAuthoringRuntimeV1(
            $propertyStore,
            $draftStore,
            $ownershipStore,
            $portfolioStore,
            new DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy(['all' => true]),
        );
        $transaction = new PostgreSqlListingTransaction($this->connection);
        $create = new DeterministicCreateListingDraftV1(
            new CreateDraft(
                new PostgreSqlListingRepository($this->connection, new ListingMapper, $transaction),
                new PropertyAuthoringCatalogAdapter($propertyStore),
                new ListingTransitionPolicy,
            ),
            new PostgreSqlListingCreationIntentStore($this->connection),
            $transaction,
        );

        return new DeterministicPropertyListingAuthoringOperations(
            $runtime,
            $create,
            $transaction,
            new class implements ListingPublicationOrchestrator
            {
                public function transition(ListingPublicationOrchestrationRequest $request): ListingPublicationOrchestrationResult
                {
                    return ListingPublicationOrchestrationResult::persistenceFailure(
                        ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure,
                    );
                }
            },
        );
    }

    private function propertyCommand(): AuthoringOperationCommand
    {
        return new AuthoringOperationCommand(
            AuthoringOperation::InitiateProperty,
            self::PROPERTY_INTENT,
            self::ACCOUNT,
            self::PROPERTY,
            null,
            0,
            [],
            new DateTimeImmutable('2026-07-27T18:00:00+00:00'),
        );
    }

    private function listingCommand(): AuthoringOperationCommand
    {
        return new AuthoringOperationCommand(
            AuthoringOperation::CreateListing,
            self::LISTING_INTENT,
            self::ACCOUNT,
            self::PROPERTY,
            self::LISTING,
            0,
            [
                'revisionId' => self::REVISION,
                'title' => 'Appartement certifié',
                'description' => 'Description complète et déterministe.',
                'transactionKind' => 'sale',
                'priceMinor' => 15000000,
                'currency' => 'XOF',
                'contactPreference' => 'platform',
            ],
            new DateTimeImmutable('2026-07-27T18:01:00+00:00'),
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
