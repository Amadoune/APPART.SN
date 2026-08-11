<?php

namespace App\Providers;

use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\OwnerPublicationReviewAuthorizationReaderV1;
use Illuminate\Support\ServiceProvider;

final class PublicationReviewAuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PublicationReviewAuthorizationReaderV1::class,
            OwnerPublicationReviewAuthorizationReaderV1::class,
        );
    }
}
