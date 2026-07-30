<?php

namespace Tests\Feature;

use App\Application\Contract\PublicListingQuery;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectTarget;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;
use Tests\Unit\Application\Support\InMemoryPublicListingQuery;

final class HistoricalRedirectHttpIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
        $this->app->instance(LoggerInterface::class, $this->createStub(LoggerInterface::class));
    }

    public function test_current_projection_returns_exact_read_model_without_qualifier_or_resolver_call(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([$listing]));
        $qualifier = $this->createMock(HistoricalCanonicalQualifier::class);
        $qualifier->expects(self::never())->method('qualify');
        $resolver = $this->createMock(HistoricalRedirectResolver::class);
        $resolver->expects(self::never())->method('resolve');
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $this->app->instance(HistoricalRedirectResolver::class, $resolver);

        $this->get('/annonces/appartement-moderne-dakar')
            ->assertOk()
            ->assertViewHas('listing', fn ($actual): bool => $actual == $listing);
    }

    public function test_resolved_historical_canonical_returns_exact_permanent_target(): void
    {
        $source = $this->canonical('ancienne-canonical');
        $target = $this->canonical('canonical-actuelle');
        $resolver = $this->historical($source);
        $resolver->expects(self::once())->method('resolve')->willReturn(
            HistoricalRedirectResolution::resolved(
                HistoricalCanonical::declared($source),
                HistoricalRedirectTarget::publicCanonical($target),
            ),
        );

        $response = $this->get('/annonces/ancienne-canonical');

        $response->assertStatus(301);
        self::assertSame($target->value, $response->headers->get('Location'));
    }

    /** @return iterable<string, array{string, int}> */
    public static function qualificationFailures(): iterable
    {
        yield 'current' => ['current', 404];
        yield 'unknown' => ['unknown', 404];
        yield 'ambiguous' => ['ambiguous', 503];
        yield 'corrupted' => ['corrupted', 503];
    }

    #[DataProvider('qualificationFailures')]
    public function test_non_historical_qualification_never_calls_resolver(string $factory, int $status): void
    {
        $canonical = $this->canonical('ancienne-canonical');
        $this->emptyCurrent();
        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willReturn(HistoricalCanonicalQualification::{$factory}($canonical));
        $resolver = $this->createMock(HistoricalRedirectResolver::class);
        $resolver->expects(self::never())->method('resolve');
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $this->app->instance(HistoricalRedirectResolver::class, $resolver);

        $response = $this->get('/annonces/ancienne-canonical');

        $response->assertStatus($status);
        self::assertNull($response->headers->get('Location'));
    }

    /** @return iterable<string, array{string, int}> */
    public static function resolverFailures(): iterable
    {
        yield 'not found' => ['notFound', 404];
        yield 'destination missing' => ['destinationMissing', 404];
        yield 'loop' => ['loopDetected', 503];
        yield 'chain' => ['chainDetected', 503];
        yield 'ambiguous' => ['ambiguous', 503];
        yield 'corrupted' => ['corrupted', 503];
    }

    #[DataProvider('resolverFailures')]
    public function test_unresolved_historical_result_never_redirects(string $factory, int $status): void
    {
        $source = $this->canonical('ancienne-canonical');
        $resolver = $this->historical($source);
        $resolver->expects(self::once())->method('resolve')->willReturn(HistoricalRedirectResolution::{$factory}(HistoricalCanonical::declared($source)));

        $response = $this->get('/annonces/ancienne-canonical');

        $response->assertStatus($status);
        self::assertNull($response->headers->get('Location'));
    }

    public function test_qualifier_and_resolver_infrastructure_failures_are_503_without_exception_exposure(): void
    {
        $this->emptyCurrent();
        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willThrowException(new PDOException('secret database qualification failure'));
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $this->app->instance(HistoricalRedirectResolver::class, $this->createStub(HistoricalRedirectResolver::class));
        $response = $this->get('/annonces/ancienne-canonical');
        $response->assertStatus(503)->assertDontSee('secret database qualification failure');
        self::assertNull($response->headers->get('Location'));

        $source = $this->canonical('ancienne-canonical');
        $resolver = $this->historical($source);
        $resolver->method('resolve')->willThrowException(new PDOException('secret database resolver failure'));
        $response = $this->get('/annonces/ancienne-canonical');
        $response->assertStatus(503)->assertDontSee('secret database resolver failure');
        self::assertNull($response->headers->get('Location'));
    }

    public function test_listing_id_has_no_public_route_and_no_location_header(): void
    {
        $listingId = '11111111-1111-4111-8111-111111111111';
        $response = $this->get('/'.$listingId);

        $response->assertNotFound();
        self::assertNull($response->headers->get('Location'));
    }

    public function test_non_resolved_outcome_is_logged_with_typed_diagnostic_code(): void
    {
        $this->emptyCurrent();
        $canonical = $this->canonical('canonical-inconnue');
        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willReturn(HistoricalCanonicalQualification::unknown($canonical));
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $this->app->instance(HistoricalRedirectResolver::class, $this->createStub(HistoricalRedirectResolver::class));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(
            'historical_redirect_http_outcome',
            ['diagnostic_code' => 'qualification_unknown'],
        );
        $this->app->instance(LoggerInterface::class, $logger);

        $this->get('/annonces/canonical-inconnue')->assertNotFound();
    }

    private function historical(CanonicalUrl $source): MockObject&HistoricalRedirectResolver
    {
        $this->emptyCurrent();
        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willReturn(HistoricalCanonicalQualification::historical($source));
        $this->app->instance(HistoricalCanonicalQualifier::class, $qualifier);
        $resolver = $this->createMock(HistoricalRedirectResolver::class);
        $this->app->instance(HistoricalRedirectResolver::class, $resolver);

        return $resolver;
    }

    private function emptyCurrent(): void
    {
        $this->app->instance(PublicListingQuery::class, new InMemoryPublicListingQuery([]));
    }

    private function canonical(string $slug): CanonicalUrl
    {
        return CanonicalUrl::fromString("https://appart.sn/annonces/{$slug}");
    }
}
