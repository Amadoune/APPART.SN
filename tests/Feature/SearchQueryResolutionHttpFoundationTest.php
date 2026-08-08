<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;
use Tests\TestCase;

final class SearchQueryResolutionHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(PublicSearchQueryResolutionReaderV1::class, new class implements PublicSearchQueryResolutionReaderV1
        {
            public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionResultV1
            {
                return SearchQueryResolutionResultV1::empty();
            }
        });
    }

    public function test_endpoint_passes_the_real_inputs_and_maps_found(): void
    {
        $this->app->instance(PublicSearchQueryResolutionReaderV1::class, new class implements PublicSearchQueryResolutionReaderV1
        {
            public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionResultV1
            {
                TestCase::assertSame('balcony dakar', $query->canonical());
                TestCase::assertSame('2026-08-02T10:00:00.123456Z', $observedAt->canonical());

                return SearchQueryResolutionResultV1::found();
            }
        });

        $this->getJson('/api/search/query-resolution?query=balcony%20dakar&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00')
            ->assertOk()
            ->assertExactJson(['status' => 'found']);
    }

    public function test_endpoint_rejects_missing_or_unknown_inputs(): void
    {
        $this->getJson('/api/search/query-resolution')->assertUnprocessable();
        $this->getJson('/api/search/query-resolution?query=x&observedAt=2026-08-02T10%3A00%3A00.123456%2B00%3A00&listingId=secret')
            ->assertUnprocessable();
    }
}
