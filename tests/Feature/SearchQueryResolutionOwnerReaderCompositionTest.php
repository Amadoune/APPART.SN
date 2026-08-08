<?php

namespace Tests\Feature;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\Contract\SearchQueryResolutionReaderV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerReader;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReader;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;
use Tests\TestCase;

final class SearchQueryResolutionOwnerReaderCompositionTest extends TestCase
{
    public function test_public_contract_has_one_singleton_owner_scoped_reader(): void
    {
        $this->app->instance(
            SearchQueryResolutionOwnerReader::class,
            new SearchQueryResolutionOwnerReader(
                $this->createStub(SearchQueryResolutionOwnerSource::class),
                new SearchQueryResolutionOwnerPolicy,
            ),
        );

        $public = $this->app->make(SearchQueryResolutionReaderV1::class);

        self::assertInstanceOf(PublicSearchQueryResolutionReader::class, $public);
        self::assertSame($public, $this->app->make(PublicSearchQueryResolutionReaderV1::class));
        self::assertSame($public, $this->app->make(SearchQueryResolutionReaderV1::class));
    }
}
