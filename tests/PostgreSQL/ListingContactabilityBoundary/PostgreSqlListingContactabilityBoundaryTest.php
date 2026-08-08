<?php

namespace Tests\PostgreSQL\ListingContactabilityBoundary;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ContactabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ListingContactabilityDecisionV1;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\OwnerListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingContactabilityBoundaryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlListingPublicationWorkflowRepository $workflows;

    private OwnerListingContactabilityReaderV1 $reader;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->workflows = new PostgreSqlListingPublicationWorkflowRepository(
            $this->connection,
            new ListingPublicationWorkflowMapper,
        );
        $this->reader = new OwnerListingContactabilityReaderV1($this->workflows);
    }

    #[Test]
    public function concrete_owner_source_maps_found_missing_and_non_contactable_without_mutation(): void
    {
        $published = ListingId::fromString('54a10000-0000-4000-8000-000000000011');
        $archived = ListingId::fromString('54a10000-0000-4000-8000-000000000012');
        $missing = ListingId::fromString('54a10000-0000-4000-8000-000000000013');
        $this->workflows->initialize($published, ListingPublicationState::Published);
        $this->workflows->initialize($archived, ListingPublicationState::Archived);
        $publishedBefore = $this->workflows->read($published)->snapshot;
        $archivedBefore = $this->workflows->read($archived)->snapshot;

        self::assertSame(
            ListingContactabilityDecisionV1::Contactable,
            $this->reader->read($published, $this->observedAt()),
        );
        self::assertSame(
            ListingContactabilityDecisionV1::NotContactable,
            $this->reader->read($archived, $this->observedAt()),
        );
        self::assertSame(
            ListingContactabilityDecisionV1::Missing,
            $this->reader->read($missing, $this->observedAt()),
        );
        self::assertEquals($publishedBefore, $this->workflows->read($published)->snapshot);
        self::assertEquals($archivedBefore, $this->workflows->read($archived)->snapshot);
    }

    private function observedAt(): ContactabilityObservedAt
    {
        return new ContactabilityObservedAt(new DateTimeImmutable('2026-07-31T12:00:00+00:00'));
    }
}
