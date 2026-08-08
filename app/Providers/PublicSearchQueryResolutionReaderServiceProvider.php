<?php

namespace App\Providers;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\Contract\SearchQueryResolutionReaderV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReader;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;
use Illuminate\Support\ServiceProvider;

final class PublicSearchQueryResolutionReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PublicSearchQueryResolutionReader::class);
        $this->app->alias(PublicSearchQueryResolutionReader::class, PublicSearchQueryResolutionReaderV1::class);
        $this->app->alias(PublicSearchQueryResolutionReader::class, SearchQueryResolutionReaderV1::class);
    }
}
