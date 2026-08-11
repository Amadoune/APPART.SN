<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use App\ReadModels\PublicListingReadModel;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;

final class PublicDiscoverabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(HistoricalCanonicalQualifier::class, $this->createStub(HistoricalCanonicalQualifier::class));
        $this->app->instance(HistoricalRedirectResolver::class, $this->createStub(HistoricalRedirectResolver::class));
    }

    public function test_home_has_one_coherent_indexable_metadata_set(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, new DiscoverabilitySearchReader(PublicSearchResultsResult::empty()));

        $response = $this->get('/')->assertOk();
        $content = $response->getContent();

        self::assertSame(1, substr_count($content, '<title>'));
        self::assertSame(1, substr_count($content, 'rel="canonical"'));
        $response->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card"', false);
    }

    public function test_search_metadata_is_dynamic_and_keeps_the_page_cursor_canonical(): void
    {
        $this->app->instance(PublicSearchResultsReaderV1::class, new DiscoverabilitySearchReader(PublicSearchResultsResult::empty()));

        $response = $this->get('/recherche?transaction=rent&city=Dakar&propertyType=apartment&after=annonces/page-un')
            ->assertOk()
            ->assertSee('<title>Appartements à louer à Dakar — APPART.SN</title>', false)
            ->assertSee('name="description" content="Découvrez les Appartements à louer à Dakar', false);

        self::assertStringContainsString('after=annonces%2Fpage-un', $response->getContent());
        self::assertSame(1, substr_count($response->getContent(), 'rel="canonical"'));
    }

    public function test_public_listing_exposes_only_projected_open_graph_twitter_and_schema_facts(): void
    {
        $listing = PublicListingReadModelFixture::make(transactionKind: 'sale', city: 'Dakar', propertyType: 'apartment');
        $this->app->instance(PublicListingQuery::class, new DiscoverabilityListingQuery($listing));

        $response = $this->get('/annonces/appartement-moderne-dakar')->assertOk();

        $response->assertSee('<meta property="og:url" content="'.$listing->canonicalUrl.'">', false)
            ->assertSee('<meta property="og:image" content="'.$listing->publicMediaUrl.'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<script type="application/ld+json">'.$listing->publicJsonLd.'</script>', false)
            ->assertDontSee('property="product:price:amount"', false)
            ->assertDontSee('telephone', false);
    }

    public function test_sitemap_contains_public_pages_and_only_indexable_canonical_listings(): void
    {
        $listing = PublicListingReadModelFixture::make(transactionKind: 'sale', city: 'Dakar');
        $summary = new PublicSearchListingSummary('annonces/appartement-moderne-dakar', $listing->listingId, $listing->headline, $listing->propertyType, $listing->publicMediaUrl);
        $this->app->instance(PublicSearchResultsReaderV1::class, new DiscoverabilitySearchReader(PublicSearchResultsResult::available([$summary], null)));
        $this->app->instance(PublicListingQuery::class, new DiscoverabilityListingQuery($listing));

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('home').'</loc>', false)
            ->assertSee('<loc>'.route('public-search.experience').'</loc>', false)
            ->assertSee('<loc>'.$listing->canonicalUrl.'</loc>', false)
            ->assertSee('<lastmod>2026-07-18</lastmod>', false);
    }
}

final readonly class DiscoverabilitySearchReader implements PublicSearchResultsReaderV1
{
    public function __construct(private PublicSearchResultsResult $result) {}

    public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult
    {
        return $this->result;
    }
}

final readonly class DiscoverabilityListingQuery implements PublicListingQuery
{
    public function __construct(private ?PublicListingReadModel $listing) {}

    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
    {
        return $this->listing;
    }
}
