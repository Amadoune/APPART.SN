<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;
use Tests\Unit\Application\Support\InMemoryPublicListingQuery;

final class PremiumPublicListingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
        $this->app->instance(HistoricalCanonicalQualifier::class, $this->createStub(HistoricalCanonicalQualifier::class));
        $this->app->instance(HistoricalRedirectResolver::class, $this->createStub(HistoricalRedirectResolver::class));
    }

    public function test_premium_page_renders_only_available_public_facts(): void
    {
        $listing = PublicListingReadModelFixture::make(transactionKind: 'sale', city: 'Dakar', propertyType: 'apartment');
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $this->get('/annonces/appartement-moderne-dakar')
            ->assertOk()
            ->assertSee('À vendre')
            ->assertSee('Appartement')
            ->assertSee('Dakar')
            ->assertSee('120 m²')
            ->assertSee('5')
            ->assertSee($listing->description)
            ->assertSee($listing->publicMediaUrl, false)
            ->assertSee('Vue principale — '.$listing->headline)
            ->assertSee('Prix non communiqué')
            ->assertSee('Contact bientôt disponible')
            ->assertDontSee('WhatsApp')
            ->assertDontSee('Envoyer un message');
    }

    public function test_unqualified_historical_listing_does_not_render_empty_transaction_or_city(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));

        $this->get('/annonces/appartement-moderne-dakar')
            ->assertOk()
            ->assertDontSee('<dt>Transaction</dt>', false)
            ->assertDontSee('<dt>Ville</dt>', false)
            ->assertDontSee('property-location', false);
    }
}
