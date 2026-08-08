<?php

namespace App\Providers;

use Appart\Modules\ContentSeo\Application\OwnerReader\ContentSeoOwnerReaderPolicy;
use Appart\Modules\ContentSeo\Application\OwnerReader\Contract\ContentSeoOwnerReaderV1;
use Appart\Modules\ContentSeo\Application\OwnerReader\EditorialContentOwnerReader;
use Appart\Modules\ContentSeo\Application\OwnerReader\OperationalSeoOwnerReader;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Illuminate\Support\ServiceProvider;

final class ContentSeoOwnerReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentSeoOwnerReaderPolicy::class);
        $this->app->alias(ContentSeoOwnerReaderPolicy::class, ContentSeoOwnerReaderV1::class);
        $this->app->singleton(EditorialContentOwnerReader::class);
        $this->app->alias(EditorialContentOwnerReader::class, EditorialContentReaderV1::class);
        $this->app->singleton(OperationalSeoOwnerReader::class);
        $this->app->alias(OperationalSeoOwnerReader::class, OperationalSeoReaderV1::class);
    }
}
