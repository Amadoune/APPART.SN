<?php

namespace Tests\Unit\ContentSeo\Http;

use App\Http\ContentSeo\ContentSeoResponseFactory;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentSeoResponseFactoryTest extends TestCase
{
    #[DataProvider('editorialCases')]
    public function test_editorial_mapping(EditorialContentResultV1 $result, int $expected): void
    {
        self::assertSame($expected, (new ContentSeoResponseFactory)->editorial($result)->getStatusCode());
    }

    /** @return iterable<string, array{EditorialContentResultV1, int}> */
    public static function editorialCases(): iterable
    {
        yield 'published' => [EditorialContentResultV1::published(), 200];
        yield 'unpublished' => [EditorialContentResultV1::unpublished(), 200];
        yield 'missing' => [EditorialContentResultV1::missing(), 404];
        yield 'corrupted' => [EditorialContentResultV1::corrupted(), 503];
        yield 'dependency unavailable' => [EditorialContentResultV1::dependencyUnavailable(), 503];
    }

    #[DataProvider('operationalCases')]
    public function test_operational_mapping(OperationalSeoResultV1 $result, int $expected): void
    {
        self::assertSame($expected, (new ContentSeoResponseFactory)->operational($result)->getStatusCode());
    }

    /** @return iterable<string, array{OperationalSeoResultV1, int}> */
    public static function operationalCases(): iterable
    {
        yield 'indexable' => [OperationalSeoResultV1::indexable(), 200];
        yield 'no index' => [OperationalSeoResultV1::noIndex(), 200];
        yield 'missing' => [OperationalSeoResultV1::missing(), 404];
        yield 'corrupted' => [OperationalSeoResultV1::corrupted(), 503];
        yield 'dependency unavailable' => [OperationalSeoResultV1::dependencyUnavailable(), 503];
    }
}
