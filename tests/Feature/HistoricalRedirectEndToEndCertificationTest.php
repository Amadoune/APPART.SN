<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\ReadModels\PublicListingReadModel;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;

final class HistoricalRedirectEndToEndCertificationTest extends TestCase
{
    private const string GENERATION = '97000000-0000-4000-8000-000000000001';

    private const string SOURCE = 'https://appart.sn/annonces/ancienne-canonical';

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

    public function test_current_projection_is_200_without_any_historical_postgresql_read(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->writeCurrent($listing);
        $this->connection->beginTransaction();
        try {
            $this->connection->exec('DROP TABLE content_seo.historical_canonical_qualifications');
            $this->connection->exec('DROP TABLE content_seo.historical_redirect_decisions');

            $this->get('/annonces/appartement-moderne-dakar')
                ->assertOk()
                ->assertViewHas('listing', fn ($actual): bool => $actual == $listing);
        } finally {
            $this->connection->rollBack();
        }
    }

    public function test_production_container_executes_complete_historical_chain_to_exact_301(): void
    {
        $target = 'https://appart.sn/annonces/canonical-actuelle';
        $this->qualification('historical');
        $this->redirect($target, 'current');

        self::assertInstanceOf(PostgreSqlHistoricalCanonicalQualifier::class, $this->app->make(HistoricalCanonicalQualifier::class));
        self::assertInstanceOf(PostgreSqlHistoricalRedirectResolver::class, $this->app->make(HistoricalRedirectResolver::class));
        self::assertInstanceOf(PublicListingQuery::class, $this->app->make(PublicListingQuery::class));
        self::assertSame(RuntimeHealthStatus::Healthy, $this->app->make(RuntimeHealthInspector::class)->inspect()->status);

        $response = $this->get('/annonces/ancienne-canonical');
        $response->assertStatus(301);
        self::assertSame($target, $response->headers->get('Location'));
    }

    /** @return iterable<string, array{string, int, string}> */
    public static function qualificationOutcomes(): iterable
    {
        yield 'current' => ['current', 404, 'qualification_current'];
        yield 'unknown' => ['unknown', 404, 'qualification_unknown'];
        yield 'ambiguous' => ['ambiguous', 503, 'qualification_ambiguous'];
        yield 'corrupted' => ['corrupted', 503, 'qualification_corrupted'];
    }

    #[DataProvider('qualificationOutcomes')]
    public function test_production_qualification_outcomes_are_http_and_observability_complete(string $scenario, int $status, string $code): void
    {
        if ($scenario === 'current') {
            $this->qualification('current');
        } elseif ($scenario === 'ambiguous') {
            $this->qualification('current');
            $this->qualification('historical', 2);
        } elseif ($scenario === 'corrupted') {
            $this->qualification('historical', checksum: str_repeat('0', 64));
        }
        $this->expectDiagnostic($code);

        $response = $this->get('/annonces/ancienne-canonical');
        $response->assertStatus($status);
        self::assertNull($response->headers->get('Location'));
    }

    /** @return iterable<string, array{string, int, string}> */
    public static function resolverOutcomes(): iterable
    {
        yield 'not found' => ['not_found', 404, 'resolver_not_found'];
        yield 'destination missing' => ['destination_missing', 404, 'destination_missing'];
        yield 'loop' => ['loop', 503, 'loop_detected'];
        yield 'chain' => ['chain', 503, 'chain_detected'];
        yield 'ambiguous' => ['ambiguous', 503, 'resolver_ambiguous'];
        yield 'corrupted' => ['corrupted', 503, 'resolver_corrupted'];
    }

    #[DataProvider('resolverOutcomes')]
    public function test_production_resolver_outcomes_are_http_and_observability_complete(string $scenario, int $status, string $code): void
    {
        $this->qualification('historical');
        if ($scenario === 'destination_missing') {
            $this->redirect(null, null);
        } elseif ($scenario === 'loop') {
            $this->redirect(self::SOURCE, 'current');
        } elseif ($scenario === 'chain') {
            $this->redirect('https://appart.sn/annonces/autre-ancienne', 'historical');
        } elseif ($scenario === 'ambiguous') {
            $this->redirect('https://appart.sn/annonces/cible-a', 'current');
            $this->redirect('https://appart.sn/annonces/cible-b', 'current', 2);
        } elseif ($scenario === 'corrupted') {
            $this->redirect('https://appart.sn/annonces/cible', 'current', checksum: str_repeat('0', 64));
        }
        $this->expectDiagnostic($code);

        $response = $this->get('/annonces/ancienne-canonical');
        $response->assertStatus($status);
        self::assertNull($response->headers->get('Location'));
    }

    public function test_qualifier_postgresql_unavailability_is_503_typed_and_not_exposed(): void
    {
        $this->connection->beginTransaction();
        try {
            $this->connection->exec('DROP TABLE content_seo.historical_canonical_qualifications');
            $this->expectDiagnostic('qualification_unavailable');
            $this->get('/annonces/ancienne-canonical')->assertStatus(503)->assertDontSee('SQLSTATE');
        } finally {
            $this->connection->rollBack();
        }

    }

    public function test_resolver_postgresql_unavailability_is_503_typed_and_not_exposed(): void
    {
        $this->qualification('historical');
        $this->connection->beginTransaction();
        try {
            $this->connection->exec('DROP TABLE content_seo.historical_redirect_decisions');
            $this->expectDiagnostic('resolver_unavailable');
            $response = $this->get('/annonces/ancienne-canonical');
            $response->assertStatus(503)->assertDontSee('SQLSTATE');
            self::assertNull($response->headers->get('Location'));
        } finally {
            $this->connection->rollBack();
        }
    }

    private function qualification(string $qualification, int $revision = 1, ?string $checksum = null): void
    {
        $row = ['decision_id' => sprintf('97100000-0000-4000-8000-%012d', $revision), 'canonical' => self::SOURCE, 'qualification' => $qualification, 'revision' => $revision];
        $row['decision_checksum'] = $checksum ?? $this->app->make(HistoricalCanonicalQualificationMapper::class)->checksum($row);
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_canonical_qualifications(decision_id,canonical,qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:canonical,:qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($row);
    }

    private function redirect(?string $destination, ?string $qualification, int $revision = 1, ?string $checksum = null): void
    {
        $row = ['decision_id' => sprintf('97200000-0000-4000-8000-%012d', $revision), 'historical_canonical' => self::SOURCE, 'destination_canonical' => $destination, 'destination_qualification' => $qualification, 'revision' => $revision];
        $row['decision_checksum'] = $checksum ?? $this->app->make(HistoricalRedirectDecisionMapper::class)->checksum($row);
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_redirect_decisions(decision_id,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:historical_canonical,:destination_canonical,:destination_qualification,:revision,:decision_checksum,\'intact\')');
        $statement->execute($row);
    }

    private function expectDiagnostic(string $code): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with('historical_redirect_http_outcome', ['diagnostic_code' => $code]);
        $this->app->instance(LoggerInterface::class, $logger);
    }

    private function writeCurrent(PublicListingReadModel $model): void
    {
        $record = PublicListingProjectionRecord::current(
            $model->listingId,
            'annonces/appartement-moderne-dakar',
            $model,
            new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1),
            PublicProjectionGenerationId::fromString(self::GENERATION),
        );
        $this->app->make(PublicListingProjectionWriter::class)->applyCurrent($record);
    }
}
