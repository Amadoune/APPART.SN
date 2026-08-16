<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;
use Tests\Unit\Application\Support\InMemoryPublicListingQuery;

final class PublicWebAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willReturnCallback(static fn ($canonical): HistoricalCanonicalQualification => HistoricalCanonicalQualification::unknown($canonical));
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $this->app->instance(HistoricalRedirectResolver::class, $this->createStub(HistoricalRedirectResolver::class));
    }

    public function test_public_page_is_served_exclusively_from_the_read_model(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $response = $this->get('/annonces/appartement-moderne-dakar');

        $response->assertOk()->assertViewIs('public-listing')->assertViewHas('listing', fn ($actual): bool => $actual == $listing);
        $response->assertSee($listing->headline);
        $response->assertSee($listing->description);
        $response->assertSee($listing->publicMediaUrl, false);
        $response->assertSee('Appartement');
        $response->assertSee('120 m²');
        $response->assertSee('5');
    }

    public function test_absent_or_unknown_canonical_returns_404(): void
    {
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([]));

        $this->get('/annonces/canonical-inconnue')->assertNotFound();
    }

    public function test_indexable_page_injects_canonical_robots_json_ld_and_breadcrumb_unchanged(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $response = $this->get('/annonces/appartement-moderne-dakar');

        $response->assertSee('<link rel="canonical" href="'.$listing->canonicalUrl.'">', false);
        $response->assertSee('<meta name="robots" content="'.$listing->htmlRobotsDirective.'">', false);
        $response->assertSee('<script type="application/ld+json">'.$listing->publicJsonLd.'</script>', false);
        foreach ($listing->breadcrumb as $item) {
            $response->assertSee('<a href="'.$item['url'].'">'.$item['label'].'</a>', false);
        }
    }

    public function test_noindex_page_injects_final_directive_and_no_json_ld(): void
    {
        $listing = PublicListingReadModelFixture::make(indexable: false);
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $response = $this->get('/annonces/appartement-moderne-dakar');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
        $response->assertDontSee('application/ld+json', false);
    }

    public function test_v2_geography_breadcrumb_is_informational_and_has_no_link(): void
    {
        $listing = PublicListingReadModelFixture::make(geographyBreadcrumb: [
            ['placeId' => 'country:sn', 'type' => 'country', 'label' => 'Senegal'],
            ['placeId' => 'region:dakar', 'type' => 'region', 'label' => 'Dakar Region'],
            ['placeId' => 'city:dakar', 'type' => 'city', 'label' => 'Dakar'],
        ]);
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $response = $this->get('/annonces/appartement-moderne-dakar');

        $response->assertOk();
        $response->assertSee('<span>Senegal</span>', false);
        $response->assertSee('<span aria-current="location">Dakar</span>', false);
        $response->assertDontSee('href="/geography/', false);
        $response->assertDontSee('href="#"', false);
    }

    public function test_listing_id_and_historical_style_paths_are_not_route_fallbacks(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $this->get('/'.$listing->listingId)->assertNotFound();
        $this->get('/annonces/ancienne-url')->assertNotFound();
    }
}
