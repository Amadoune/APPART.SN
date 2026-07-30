<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\ReadModels\PublicListingReadModel;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;

final class PublicProjectionHttpRuntimeCertificationTest extends TestCase
{
    private const string GENERATION = '96000000-0000-4000-8000-000000000001';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
        $this->connection->exec("INSERT INTO public_projection.generations(generation_id,state) VALUES ('".self::GENERATION."','active')");
    }

    public function test_current_canonical_is_served_from_the_production_projection_store(): void
    {
        $model = PublicListingReadModelFixture::make();
        $this->write($model, 'annonces/appartement-moderne-dakar');

        self::assertSame($this->app->make(PublicListingQuery::class)::class, $this->app->make(PublicListingProjectionWriter::class)::class);
        $response = $this->get('/annonces/appartement-moderne-dakar');

        $response->assertOk()->assertViewIs('public-listing')->assertViewHas('listing', fn ($actual): bool => $actual == $model);
        $response->assertSee($model->headline)->assertSee($model->description);
        $response->assertSee('<link rel="canonical" href="'.$model->canonicalUrl.'">', false);
        $response->assertSee('<script type="application/ld+json">'.$model->publicJsonLd.'</script>', false);
    }

    public function test_unknown_historical_and_listing_id_paths_are_never_served_as_current(): void
    {
        $historical = PublicListingReadModelFixture::make(canonicalUrl: 'https://appart.sn/annonces/ancienne-canonical');
        $this->write($historical, 'annonces/ancienne-canonical');
        $current = PublicListingReadModelFixture::make(canonicalUrl: 'https://appart.sn/annonces/canonical-actuelle');
        $this->app->make(PublicListingProjectionWriter::class)->replaceCanonical('annonces/ancienne-canonical', $this->record($current, 'annonces/canonical-actuelle', 2));

        $this->get('/annonces/canonical-inconnue')->assertNotFound();
        $this->get('/annonces/ancienne-canonical')->assertNotFound();
        $this->get('/'.$current->listingId)->assertNotFound();
    }

    public function test_historical_canonical_uses_production_qualifier_and_resolver_for_exact_redirect(): void
    {
        $source = 'https://appart.sn/annonces/ancienne-canonical';
        $target = 'https://appart.sn/annonces/canonical-actuelle';
        $qualification = ['decision_id' => '96100000-0000-4000-8000-000000000001', 'canonical' => $source, 'qualification' => 'historical', 'revision' => 1];
        $qualification['decision_checksum'] = (new HistoricalCanonicalQualificationMapper)->checksum($qualification);
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_canonical_qualifications(decision_id,canonical,qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:canonical,:qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($qualification);

        $redirect = ['decision_id' => '96200000-0000-4000-8000-000000000001', 'historical_canonical' => $source, 'destination_canonical' => $target, 'destination_qualification' => 'current', 'revision' => 1];
        $redirect['decision_checksum'] = (new HistoricalRedirectDecisionMapper)->checksum($redirect);
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_redirect_decisions(decision_id,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:historical_canonical,:destination_canonical,:destination_qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($redirect);

        self::assertInstanceOf(PostgreSqlHistoricalCanonicalQualifier::class, $this->app->make(HistoricalCanonicalQualifier::class));
        self::assertInstanceOf(PostgreSqlHistoricalRedirectResolver::class, $this->app->make(HistoricalRedirectResolver::class));
        $this->get('/annonces/ancienne-canonical')->assertRedirect($target)->assertStatus(301);
    }

    public function test_noindex_and_json_ld_are_copied_from_the_durable_projection(): void
    {
        $model = PublicListingReadModelFixture::make(indexable: false);
        $this->write($model, 'annonces/appartement-moderne-dakar');

        $response = $this->get('/annonces/appartement-moderne-dakar');
        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
        $response->assertDontSee('application/ld+json', false);
    }

    public function test_corrupt_projection_fails_safe_without_recalculation(): void
    {
        $this->write(PublicListingReadModelFixture::make(), 'annonces/appartement-moderne-dakar');
        $this->connection->exec("UPDATE public_projection.listing_projections SET payload_checksum='".str_repeat('0', 64)."' WHERE canonical_path='annonces/appartement-moderne-dakar'");

        $this->get('/annonces/appartement-moderne-dakar')->assertStatus(503);
    }

    public function test_projection_store_unavailability_is_an_explicit_503(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->exec('DROP TABLE public_projection.listing_projections');
            $this->get('/annonces/appartement-moderne-dakar')->assertStatus(503);
        } finally {
            $this->connection->rollBack();
        }
    }

    private function write(PublicListingReadModel $model, string $canonical): void
    {
        $this->app->make(PublicListingProjectionWriter::class)->applyCurrent($this->record($model, $canonical));
    }

    private function record(PublicListingReadModel $model, string $canonical, int $version = 1): PublicListingProjectionRecord
    {
        return PublicListingProjectionRecord::current(
            $model->listingId,
            $canonical,
            $model,
            new PublicProjectionWatermark($version, $version, $version, $version, $version, $version, $version),
            PublicProjectionGenerationId::fromString(self::GENERATION),
        );
    }
}
