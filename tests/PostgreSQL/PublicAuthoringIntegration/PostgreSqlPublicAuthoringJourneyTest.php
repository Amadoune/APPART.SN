<?php

namespace Tests\PostgreSQL\PublicAuthoringIntegration;

use App\Application\PublicAuthoringIntegration\Contract\PublicAuthoringJourney;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyOperation;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyRequest;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyStatus;
use DateTimeImmutable;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlPublicAuthoringJourneyTest extends TestCase
{
    private const string ACCOUNT = '76000000-0000-4000-8000-000000000001';

    private const string PROPERTY = '76000000-0000-4000-8000-000000000002';

    private const string LISTING = '76000000-0000-4000-8000-000000000003';

    private const string REVISION = '76000000-0000-4000-8000-000000000004';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
    }

    public function test_public_journey_reaches_complete_atomic_listing_creation(): void
    {
        $journey = $this->app->make(PublicAuthoringJourney::class);
        $property = $journey->execute($this->request(
            PublicAuthoringJourneyOperation::InitiateProperty,
            '76000000-0000-4000-8000-000000000005',
            self::PROPERTY,
            null,
            [],
        ));
        $listing = $journey->execute($this->request(
            PublicAuthoringJourneyOperation::CreateListing,
            '76000000-0000-4000-8000-000000000006',
            self::PROPERTY,
            self::LISTING,
            [
                'revisionId' => self::REVISION,
                'title' => 'Parcours public certifié',
                'description' => 'Description complète.',
                'transactionKind' => 'sale',
                'priceMinor' => 25000000,
                'currency' => 'XOF',
                'contactPreference' => 'platform',
            ],
        ));

        self::assertSame(PublicAuthoringJourneyStatus::Succeeded, $property->status);
        self::assertSame(PublicAuthoringJourneyStatus::Succeeded, $listing->status);
        self::assertSame(1, $this->tableCount('listing_lifecycle.listings'));
        self::assertSame(1, $this->tableCount('listing_authoring.drafts'));
        self::assertSame(1, $this->tableCount('listing_authoring.ownerships'));
        self::assertSame(1, $this->tableCount('listing_authoring.portfolio_items'));
    }

    /** @param array<string, mixed> $data */
    private function request(
        PublicAuthoringJourneyOperation $operation,
        string $intentId,
        ?string $propertyId,
        ?string $listingId,
        array $data,
    ): PublicAuthoringJourneyRequest {
        return new PublicAuthoringJourneyRequest(
            $operation,
            $intentId,
            self::ACCOUNT,
            $propertyId,
            $listingId,
            0,
            $data,
            new DateTimeImmutable('2026-07-27T20:00:00+00:00'),
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
