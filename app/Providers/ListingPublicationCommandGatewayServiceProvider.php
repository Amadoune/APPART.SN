<?php

namespace App\Providers;

use App\Application\ListingPublicationCommandGateway\DeterministicListingPublicationCommandGateway;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandLedgerV1;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationGatewayTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationGatewayPersistence;
use Illuminate\Support\ServiceProvider;

final class ListingPublicationCommandGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlListingPublicationGatewayPersistence::class);
        $this->app->alias(PostgreSqlListingPublicationGatewayPersistence::class, ListingPublicationCommandLedgerV1::class);
        $this->app->alias(PostgreSqlListingPublicationGatewayPersistence::class, ListingPublicationGatewayTransaction::class);
        $this->app->singleton(DeterministicListingPublicationCommandGateway::class);
        $this->app->alias(DeterministicListingPublicationCommandGateway::class, ListingPublicationCommandGatewayV1::class);
    }
}
