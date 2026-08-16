<?php

namespace Tests\PostgreSQL\AuthoringOperations;

use App\Application\ListingPublicationEventIntegration\AtomicListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayResult;
use App\Application\PropertyAuthoringGeographySelection\GeographySelectionReplayStatus;
use App\Application\PropertyAuthoringSourceCompleteness\DeterministicPropertyAuthoringStateEnricherV1;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\DeterministicPropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeAvailabilityPolicy;
use App\Application\PropertyListingAuthoringRuntime\DeterministicPropertyListingAuthoringRuntimeV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\Authoring\PropertyAuthoringCatalogAdapter;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceRepository;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\DeterministicListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\AuthoringPortfolioMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingDraftMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingOwnershipMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPortfolioStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlAuthoringPublicFactHandoff;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewConsumer;
use Appart\Modules\PublicationReview\Infrastructure\Persistence\PostgreSql\PostgreSqlPublicationReviewQueue;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\DeterministicAddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\UtcCalendarBusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\DeterministicPromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyCommand;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyStatus;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use Appart\Modules\RealEstateCatalog\Infrastructure\Geography\GeographyBackedGeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionParticipantTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyAuthoringMapper;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
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
        $this->seedGeography();
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
        self::assertSame(1, $this->tableCount('listing_lifecycle.publication_workflow_transitions'));
        $property = (new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper))->read(self::PROPERTY);
        self::assertNotNull($property);
        self::assertSame('PROPERTY-650', $property->propertyReference);
        self::assertSame('65000000-0000-4000-8000-000000000010', $property->geographicPlaceId);
        self::assertNotNull($property->addressIntentId);
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

    public function test_created_listing_can_be_submitted_through_the_initialized_workflow(): void
    {
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);

        $submitted = $operations->execute(new AuthoringOperationCommand(
            AuthoringOperation::SubmitListing,
            '65000000-0000-4000-8000-000000000007',
            self::ACCOUNT,
            null,
            self::LISTING,
            1,
            [],
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
            1,
        ));

        self::assertSame(AuthoringOperationStatus::Applied, $submitted->status);
        self::assertSame(2, $this->tableCount('listing_lifecycle.publication_workflow_transitions'));
        self::assertSame(
            ListingStatus::Submitted,
            (new PostgreSqlListingRepository($this->connection, new ListingMapper, new PostgreSqlListingTransaction($this->connection)))
                ->find(ListingId::fromString(self::LISTING))?->status(),
        );
    }

    public function test_submit_stays_draft_when_property_promotion_is_rejected(): void
    {
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);

        $places = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $place = $places->find(PlaceId::fromString('65000000-0000-4000-8000-000000000010'));
        self::assertNotNull($place);
        $place->disable(new DateTimeImmutable('2026-07-27T18:01:30+00:00'));
        $places->save($place, 1);

        $result = $operations->execute(new AuthoringOperationCommand(
            AuthoringOperation::SubmitListing,
            '65000000-0000-4000-8000-000000000007',
            self::ACCOUNT,
            null,
            self::LISTING,
            1,
            [],
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
            1,
        ));

        self::assertSame(AuthoringOperationStatus::LifecycleConflict, $result->status);
        self::assertSame(
            ListingStatus::Draft,
            (new PostgreSqlListingRepository($this->connection, new ListingMapper, new PostgreSqlListingTransaction($this->connection)))
                ->find(ListingId::fromString(self::LISTING))?->status(),
        );
        self::assertSame(0, $this->tableCount('real_estate_catalog.properties'));
    }

    public function test_closed_workflow_failure_rolls_back_listing_and_local_handoffs_then_retry_converges(): void
    {
        $failing = new FailAfterAppliedListingPublicationEventOrchestrator($this->publicationOrchestrator());
        $operations = $this->operations($failing);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);

        self::assertSame(AuthoringOperationStatus::DependencyUnavailable, $operations->execute($this->submitCommand())->status);
        self::assertSame(ListingStatus::Draft, $this->listingStatus());
        self::assertSame(1, $this->tableCount('listing_lifecycle.publication_workflow_transitions'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.authoring_public_fact_handoffs'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.public_projection_outbox_messages'));
        self::assertSame(0, $this->tableCount('listing_lifecycle.public_projection_outbox_deliveries'));
        self::assertSame(0, $this->tableCount('publication_review.queue_items'));
        self::assertSame(1, $this->tableCount('real_estate_catalog.properties'));
        self::assertSame(1, $this->tableCount('real_estate_catalog.property_promotion_commands'));

        self::assertSame(AuthoringOperationStatus::Applied, $this->operations()->execute($this->submitCommand())->status);
        self::assertSame(ListingStatus::Submitted, $this->listingStatus());
        self::assertSame(2, $this->tableCount('listing_lifecycle.publication_workflow_transitions'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.authoring_public_fact_handoffs'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.public_projection_outbox_messages'));
        self::assertSame(1, $this->tableCount('listing_lifecycle.public_projection_outbox_deliveries'));
        self::assertSame(1, $this->tableCount('publication_review.queue_items'));
        self::assertSame(1, $this->tableCount('real_estate_catalog.properties'));
        self::assertSame(1, $this->tableCount('real_estate_catalog.property_promotion_commands'));
    }

    public function test_submit_accepts_only_canonical_ledgerless_existing_property(): void
    {
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);
        self::assertSame(PromoteAuthoredPropertyStatus::Applied, $this->promotion()->promote(new PromoteAuthoredPropertyCommand(
            self::PROPERTY,
            self::ACCOUNT,
            1,
            '65000000-0000-4000-8000-000000000008',
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
        ))->status);

        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->submitCommand())->status);
        self::assertSame(ListingStatus::Submitted, $this->listingStatus());
        self::assertSame(1, $this->tableCount('real_estate_catalog.properties'));
        self::assertSame(1, $this->tableCount('real_estate_catalog.property_promotion_commands'));

        PostgreSqlTestEnvironment::reset($this->connection);
        $this->seedGeography();
        $operations = $this->operations();
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->propertyCommand())->status);
        self::assertSame(AuthoringOperationStatus::Applied, $operations->execute($this->listingCommand())->status);
        $properties = new PostgreSqlPropertyRepository($this->connection, new PropertyMapper, new PostgreSqlPropertyTransaction($this->connection));
        $properties->add($this->existingProperty(AddressId::fromString('65000000-0000-4000-8000-000000000099')));

        self::assertSame(AuthoringOperationStatus::DivergentIntent, $operations->execute($this->submitCommand())->status);
        self::assertSame(ListingStatus::Draft, $this->listingStatus());
        self::assertSame(1, $this->tableCount('real_estate_catalog.properties'));
        self::assertSame(0, $this->tableCount('real_estate_catalog.property_promotion_commands'));
    }

    private function operations(?ListingPublicationEventOrchestrator $publication = null): DeterministicPropertyListingAuthoringOperations
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
        $registry = new PostgreSqlListingRepository($this->connection, new ListingMapper, $transaction);
        $properties = new PropertyAuthoringCatalogAdapter($propertyStore);
        $create = new DeterministicCreateListingDraftV1(
            new CreateDraft(
                $registry,
                $properties,
                new ListingTransitionPolicy,
            ),
            new PostgreSqlListingCreationIntentStore($this->connection),
            $transaction,
            new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper),
        );

        $workflows = new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper);

        return new DeterministicPropertyListingAuthoringOperations(
            $runtime,
            $create,
            $transaction,
            $publication ?? $this->publicationOrchestrator($workflows),
            new DeterministicPropertyAuthoringStateEnricherV1(new class implements GeographySelectionReplayValidatorV1
            {
                public function validate(string $geographicPlaceId, string $type, ?string $parentPlaceId, ?string $cursor, int $limit): GeographySelectionReplayResult
                {
                    return new GeographySelectionReplayResult(GeographySelectionReplayStatus::Validated);
                }
            }),
            new PostgreSqlAuthoringPublicFactHandoff($this->connection),
            new SubmitListing($registry, $properties, new ListingTransitionPolicy),
            new DeterministicListingRevisionAllocatorV1,
            $this->promotion(),
        );
    }

    private function promotion(): DeterministicPromoteAuthoredPropertyV1
    {
        $propertyStore = new PostgreSqlPropertyAuthoringStore($this->connection, new PropertyAuthoringMapper);
        $places = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $properties = new PostgreSqlPropertyRepository($this->connection, new PropertyMapper, new PostgreSqlPromotionParticipantTransaction($this->connection));

        return new DeterministicPromoteAuthoredPropertyV1(
            $propertyStore,
            $properties,
            new RegisterProperty($properties, new GeographyBackedGeographicPlaceCatalog($places, new GeographicPlaceAddressabilityPolicy), new PropertyTypePolicy),
            new DeterministicAddressIdentityIssuerV1,
            new UtcCalendarBusinessYearAuthorityV1,
            new PostgreSqlPromotionCommandLedger($this->connection),
            new PostgreSqlPromotionTransaction($this->connection),
        );
    }

    private function existingProperty(AddressId $addressId): Property
    {
        return Property::reconstitute(
            PropertyId::fromString(self::PROPERTY),
            PropertyReference::fromString('PROPERTY-650'),
            PropertyType::Apartment,
            SurfaceArea::fromSquareMeters(120),
            RoomCount::fromInt(4),
            BathroomCount::fromInt(2),
            ConstructionYear::fromInt(2020),
            new Address($addressId, GeographicPlaceId::fromString('65000000-0000-4000-8000-000000000010'), AddressLine::fromString('12 avenue Cheikh Anta Diop')),
            PropertyStatus::Active,
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
            0,
        );
    }

    private function publicationOrchestrator(?PostgreSqlListingPublicationWorkflowRepository $workflows = null): AtomicListingPublicationEventOrchestrator
    {
        return new AtomicListingPublicationEventOrchestrator(
            new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $workflows ?? new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper)),
            new PostgreSqlAggregateOutboxTransaction($this->connection),
            new ListingPublicationEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper),
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
            new PublicationReviewConsumer(new PostgreSqlPublicationReviewQueue($this->connection)),
        );
    }

    private function submitCommand(): AuthoringOperationCommand
    {
        return new AuthoringOperationCommand(
            AuthoringOperation::SubmitListing,
            '65000000-0000-4000-8000-000000000007',
            self::ACCOUNT,
            null,
            self::LISTING,
            1,
            [],
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
            1,
        );
    }

    private function listingStatus(): ?ListingStatus
    {
        return (new PostgreSqlListingRepository($this->connection, new ListingMapper, new PostgreSqlListingTransaction($this->connection)))
            ->find(ListingId::fromString(self::LISTING))?->status();
    }

    private function seedGeography(): void
    {
        $places = new PostgreSqlPlaceRepository($this->connection, new PlaceMapper);
        $at = new DateTimeImmutable('2026-07-27T17:59:00+00:00');
        $country = Place::create(PlaceId::fromString('65000000-0000-4000-8000-000000000012'), PlaceName::fromString('Senegal'), PlaceCode::fromString('SN'), PlaceType::Country, CountryCode::fromString('SN'), $at);
        $region = Place::create(PlaceId::fromString('65000000-0000-4000-8000-000000000011'), PlaceName::fromString('Dakar Region'), PlaceCode::fromString('DKR'), PlaceType::Region, CountryCode::fromString('SN'), $at, $country);
        $city = Place::create(PlaceId::fromString('65000000-0000-4000-8000-000000000010'), PlaceName::fromString('Dakar'), PlaceCode::fromString('DKC'), PlaceType::City, CountryCode::fromString('SN'), $at, $region);

        foreach ([$country, $region, $city] as $place) {
            $places->add($place);
        }
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
            [
                'propertyType' => 'apartment',
                'city' => 'Dakar',
                'neighborhood' => 'Almadies',
                'propertyReference' => 'PROPERTY-650',
                'surfaceSquareMeters' => 120,
                'rooms' => 4,
                'bathrooms' => 2,
                'constructionYear' => 2020,
                'geographicPlaceId' => '65000000-0000-4000-8000-000000000010',
                'geographicPlaceType' => 'neighborhood',
                'geographicParentPlaceId' => '65000000-0000-4000-8000-000000000011',
                'geographicSelectionCursor' => null,
                'geographicSelectionLimit' => 50,
                'addressLine' => '12 avenue Cheikh Anta Diop',
            ],
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

final readonly class FailAfterAppliedListingPublicationEventOrchestrator implements ListingPublicationEventOrchestrator
{
    public function __construct(private ListingPublicationEventOrchestrator $delegate) {}

    public function transition(ListingPublicationEventOrchestrationRequest $request): ListingPublicationOrchestrationResult
    {
        $this->delegate->transition($request);

        return ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure);
    }
}
