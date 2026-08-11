<?php

namespace Tests\Feature;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use Tests\TestCase;

final class PublicSearchResultsHttpTest extends TestCase
{
    public function test_endpoint_exposes_only_qualified_public_fields(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, new class implements PublicSearchResultsReaderV1
        {
            public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
            {
                return PublicSearchResultsResult::available([
                    new PublicSearchListingSummary('annonces/appartement-dakar', 'listing-public', 'Appartement à Dakar', 'Appartement', null, 'sale', 'Dakar', 120, 5),
                ], 'annonces/appartement-dakar');
            }
        });

        $this->getJson('/api/public-search/results?limit=1')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertExactJson([
                'status' => 'available',
                'items' => [[
                    'canonicalPath' => 'annonces/appartement-dakar',
                    'listingId' => 'listing-public',
                    'headline' => 'Appartement à Dakar',
                    'propertyType' => 'Appartement',
                    'primaryImageUrl' => null,
                    'transaction' => 'sale',
                    'city' => 'Dakar',
                    'surfaceSquareMeters' => 120,
                    'roomCount' => 5,
                ]],
                'nextCursor' => 'annonces/appartement-dakar',
            ]);
    }

    public function test_empty_result_is_http_200(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, new class implements PublicSearchResultsReaderV1
        {
            public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
            {
                return PublicSearchResultsResult::empty();
            }
        });

        $this->getJson('/api/public-search/results')->assertOk()->assertJson(['status' => 'empty', 'items' => []]);
    }

    public function test_unknown_or_unsupported_filters_are_rejected(): void
    {
        $this->getJson('/api/public-search/results?budget=100000')->assertUnprocessable();
        $this->getJson('/api/public-search/results?transaction=buy')->assertUnprocessable();
    }

    public function test_dependency_unavailable_is_http_503(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, new class implements PublicSearchResultsReaderV1
        {
            public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
            {
                return PublicSearchResultsResult::dependencyUnavailable();
            }
        });

        $this->getJson('/api/public-search/results')->assertServiceUnavailable()->assertJson(['status' => 'dependency_unavailable']);
    }
}
