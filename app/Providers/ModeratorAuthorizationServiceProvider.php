<?php

namespace App\Providers;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\OwnerModeratorAuthorizationReaderV1;
use Illuminate\Support\ServiceProvider;

final class ModeratorAuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OwnerModeratorAuthorizationReaderV1::class);
        $this->app->alias(OwnerModeratorAuthorizationReaderV1::class, ModeratorAuthorizationReaderV1::class);
    }
}
