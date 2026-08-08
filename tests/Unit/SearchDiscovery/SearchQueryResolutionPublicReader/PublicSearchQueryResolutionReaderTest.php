<?php

namespace Tests\Unit\SearchDiscovery\SearchQueryResolutionPublicReader;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionStatusV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicSearchQueryResolutionReaderTest extends TestCase
{
    #[DataProvider('mappings')]
    public function test_mapping_is_exhaustive_bijective_and_mechanical(
        SearchQueryResolutionOwnerStatus $ownerStatus,
        PublicSearchQueryResolutionReaderStatus $readerStatus,
        SearchQueryResolutionStatusV1 $publicStatus,
    ): void {
        $result = (new PublicSearchQueryResolutionReaderPolicy)
            ->reduce(new SearchQueryResolutionOwnerResult($ownerStatus));

        self::assertSame($readerStatus, $result->status);
        self::assertSame($publicStatus, $result->toPublicResultV1()->status);
    }

    public static function mappings(): iterable
    {
        yield [SearchQueryResolutionOwnerStatus::Found, PublicSearchQueryResolutionReaderStatus::Found, SearchQueryResolutionStatusV1::Found];
        yield [SearchQueryResolutionOwnerStatus::Empty, PublicSearchQueryResolutionReaderStatus::Empty, SearchQueryResolutionStatusV1::Empty];
        yield [SearchQueryResolutionOwnerStatus::Corrupted, PublicSearchQueryResolutionReaderStatus::Corrupted, SearchQueryResolutionStatusV1::Corrupted];
        yield [SearchQueryResolutionOwnerStatus::DependencyUnavailable, PublicSearchQueryResolutionReaderStatus::DependencyUnavailable, SearchQueryResolutionStatusV1::DependencyUnavailable];
    }
}
