<?php

namespace Tests\Unit\Http;

use App\Application\Contract\PublicListingQuery;
use App\Http\Controllers\PublicListingController;
use App\ReadModels\PublicListingReadModel;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Support\PublicListingReadModelFixture;
use Tests\TestCase;

final class PublicListingControllerTest extends TestCase
{
    public function test_controller_forwards_the_exact_canonical_path_and_unchanged_read_model(): void
    {
        $listing = PublicListingReadModelFixture::make();
        $query = new class($listing) implements PublicListingQuery
        {
            public ?string $receivedPath = null;

            public function __construct(private readonly PublicListingReadModel $listing) {}

            public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
            {
                $this->receivedPath = $canonicalPath;

                return $this->listing;
            }
        };

        $qualifier = $this->createMock(HistoricalCanonicalQualifier::class);
        $qualifier->expects(self::never())->method('qualify');
        $view = (new PublicListingController($query, $qualifier, $this->createStub(HistoricalRedirectResolver::class), new NullLogger))('annonces/appartement-moderne-dakar');

        self::assertSame('annonces/appartement-moderne-dakar', $query->receivedPath);
        self::assertSame('public-listing', $view->name());
        self::assertSame($listing, $view->getData()['listing']);
    }

    public function test_controller_throws_404_when_query_returns_no_model(): void
    {
        $query = new class implements PublicListingQuery
        {
            public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel
            {
                return null;
            }
        };

        $this->expectException(NotFoundHttpException::class);

        $qualifier = $this->createStub(HistoricalCanonicalQualifier::class);
        $qualifier->method('qualify')->willReturnCallback(static fn ($canonical): HistoricalCanonicalQualification => HistoricalCanonicalQualification::unknown($canonical));

        (new PublicListingController($query, $qualifier, $this->createStub(HistoricalRedirectResolver::class), new NullLogger))('annonces/absente');
    }
}
