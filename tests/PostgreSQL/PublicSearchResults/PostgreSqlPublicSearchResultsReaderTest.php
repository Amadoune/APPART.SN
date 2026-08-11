<?php

namespace Tests\PostgreSQL\PublicSearchResults;

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsStatus;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicSearchResultsReader;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;

final class PostgreSqlPublicSearchResultsReaderTest extends TestCase
{
    private const GENERATION = '99000000-0000-4000-8000-000000000001';

    private PDO $connection;

    private PostgreSqlPublicListingProjectionMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->connection->exec("INSERT INTO public_projection.generations (generation_id,state) VALUES ('".self::GENERATION."','active')");
        $this->mapper = new PostgreSqlPublicListingProjectionMapper;
    }

    protected function tearDown(): void
    {
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_reader_returns_deterministic_bounded_pages_from_active_public_projection(): void
    {
        $this->write('annonces/a-dakar', '91000000-0000-4000-8000-000000000001');
        $this->write('annonces/b-dakar', '91000000-0000-4000-8000-000000000002');

        $first = $this->reader()->read(new PublicSearchResultsQuery(1));
        self::assertSame(PublicSearchResultsStatus::Available, $first->status);
        self::assertSame('annonces/a-dakar', $first->items[0]->canonicalPath);
        self::assertSame('annonces/a-dakar', $first->nextCursor);

        $second = $this->reader()->read(new PublicSearchResultsQuery(1, $first->nextCursor));
        self::assertSame('annonces/b-dakar', $second->items[0]->canonicalPath);
        self::assertNull($second->nextCursor);
    }

    public function test_reader_returns_empty_without_active_public_rows(): void
    {
        self::assertSame(PublicSearchResultsStatus::Empty, $this->reader()->read(new PublicSearchResultsQuery)->status);
    }

    public function test_reader_filters_transaction_city_and_type_before_pagination(): void
    {
        $this->write('annonces/a-dakar', '91000000-0000-4000-8000-000000000001', 'sale', 'Dakar', 'Appartement');
        $this->write('annonces/b-dakar', '91000000-0000-4000-8000-000000000002', 'rent', 'Dakar', 'Appartement');
        $this->write('annonces/c-thies', '91000000-0000-4000-8000-000000000003', 'sale', 'Thiès', 'Villa');

        $result = $this->reader()->read(new PublicSearchResultsQuery(1, null, 'sale', 'Dakar', 'Appartement'));

        self::assertSame(PublicSearchResultsStatus::Available, $result->status);
        self::assertCount(1, $result->items);
        self::assertSame('annonces/a-dakar', $result->items[0]->canonicalPath);
        self::assertSame('sale', $result->items[0]->transaction);
        self::assertNull($result->nextCursor);
    }

    public function test_corrupt_projection_is_fail_closed(): void
    {
        $this->write('annonces/a-dakar', '91000000-0000-4000-8000-000000000001');
        $this->connection->exec("UPDATE public_projection.listing_projections SET payload_checksum='".str_repeat('0', 64)."'");

        self::assertSame(PublicSearchResultsStatus::Corrupted, $this->reader()->read(new PublicSearchResultsQuery)->status);
    }

    private function reader(): PostgreSqlPublicSearchResultsReader
    {
        return new PostgreSqlPublicSearchResultsReader($this->connection, $this->mapper);
    }

    private function write(string $canonicalPath, string $listingId, ?string $transaction = null, ?string $city = null, string $propertyType = 'Appartement'): void
    {
        $model = PublicListingReadModelFixture::make(listingId: $listingId, canonicalUrl: 'https://appart.sn/'.$canonicalPath, transactionKind: $transaction, city: $city, propertyType: $propertyType);
        $record = PublicListingProjectionRecord::current(
            $listingId,
            $canonicalPath,
            $model,
            new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1),
            PublicProjectionGenerationId::fromString(self::GENERATION),
        );
        $reader = new PostgreSqlPublicListingProjectionReader($this->connection, $this->mapper);
        (new PostgreSqlPublicListingProjectionWriter($this->connection, $this->mapper, $reader))->applyCurrent($record);
    }
}
