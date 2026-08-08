<?php

namespace Tests\Unit\SearchQueryResolutionHttp;

use App\Http\SearchQueryResolution\SearchQueryResolutionResponseFactory;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchQueryResolutionResponseFactoryTest extends TestCase
{
    #[DataProvider('responses')]
    public function test_http_mapping_is_closed_and_mechanical(
        SearchQueryResolutionResultV1 $result,
        int $status,
        string $bodyStatus,
    ): void {
        $response = (new SearchQueryResolutionResponseFactory)->make($result);

        self::assertSame($status, $response->getStatusCode());
        self::assertSame(['status' => $bodyStatus], $response->getData(true));
    }

    public static function responses(): iterable
    {
        yield [SearchQueryResolutionResultV1::found(), 200, 'found'];
        yield [SearchQueryResolutionResultV1::empty(), 200, 'empty'];
        yield [SearchQueryResolutionResultV1::corrupted(), 503, 'corrupted'];
        yield [SearchQueryResolutionResultV1::dependencyUnavailable(), 503, 'dependency_unavailable'];
    }
}
