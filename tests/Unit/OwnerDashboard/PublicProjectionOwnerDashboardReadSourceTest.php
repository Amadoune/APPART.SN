<?php

namespace Tests\Unit\OwnerDashboard;

use App\Application\Contract\PublicListingQuery;
use App\Application\OwnerDashboard\OwnerDashboardReadStatus;
use App\Application\OwnerDashboard\PublicProjectionOwnerDashboardReadSource;
use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use App\ReadModels\PublicListingReadModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\PublicListingReadModelFixture;

final class PublicProjectionOwnerDashboardReadSourceTest extends TestCase
{
    #[Test]
    public function it_maps_only_certified_public_projection_facts(): void
    {
        $summary = new PublicSearchListingSummary(
            'annonces/appartement-moderne-dakar',
            '91000000-0000-4000-8000-000000000001',
            'Appartement moderne à Dakar',
            'apartment',
            'https://media.appart.sn/listings/primary.webp',
            'sale',
            'Dakar',
        );
        $source = new PublicProjectionOwnerDashboardReadSource(
            new DashboardSearchReader(PublicSearchResultsResult::available([$summary], null)),
            new DashboardListingQuery(PublicListingReadModelFixture::make(transactionKind: 'sale', city: 'Dakar', propertyType: 'apartment')),
        );

        $result = $source->read();

        self::assertSame(OwnerDashboardReadStatus::Available, $result->status);
        self::assertCount(1, $result->listings);
        self::assertSame('public', $result->listings[0]->visibility);
        self::assertSame('published', $result->listings[0]->status);
        self::assertSame('sale', $result->listings[0]->transaction);
        self::assertSame('Dakar', $result->listings[0]->city);
    }

    #[Test]
    public function it_fails_closed_when_public_details_are_missing(): void
    {
        $summary = new PublicSearchListingSummary('annonces/missing', '91000000-0000-4000-8000-000000000001', null, 'apartment', null);
        $source = new PublicProjectionOwnerDashboardReadSource(
            new DashboardSearchReader(PublicSearchResultsResult::available([$summary], null)),
            new DashboardListingQuery(null),
        );

        self::assertSame(OwnerDashboardReadStatus::Unavailable, $source->read()->status);
    }
}

final readonly class DashboardSearchReader implements PublicSearchResultsReaderV1
{
    public function __construct(private PublicSearchResultsResult $result) {}

    public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
    {
        return $this->result;
    }
}

final readonly class DashboardListingQuery implements PublicListingQuery
{
    public function __construct(private ?PublicListingReadModel $listing) {}

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        return $this->listing;
    }
}
