<?php

namespace App\Providers;

use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\Contract\AddressIdentityIssuerV1;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract\BusinessYearAuthorityV1;
use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionTransaction;
use Appart\Modules\RealEstateCatalog\Application\Promotion\DeterministicPromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\UseCase\RegisterProperty;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionCommandLedger;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionParticipantTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPromotionTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PDO;

final class PublicPropertyPromotionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPromotionTransaction::class);
        $this->app->alias(PostgreSqlPromotionTransaction::class, PromotionTransaction::class);
        $this->app->singleton(PostgreSqlPromotionCommandLedger::class);
        $this->app->alias(PostgreSqlPromotionCommandLedger::class, PromotionCommandLedger::class);
        $this->app->singleton(PostgreSqlPromotionParticipantTransaction::class);
        $this->app->singleton(
            DeterministicPromoteAuthoredPropertyV1::class,
            static function (Application $app): DeterministicPromoteAuthoredPropertyV1 {
                $properties = new PostgreSqlPropertyRepository(
                    $app->make(PDO::class),
                    $app->make(PropertyMapper::class),
                    $app->make(PostgreSqlPromotionParticipantTransaction::class),
                );

                return new DeterministicPromoteAuthoredPropertyV1(
                    $app->make(PropertyAuthoringStore::class),
                    $properties,
                    new RegisterProperty($properties, $app->make(GeographicPlaceCatalog::class), new PropertyTypePolicy),
                    $app->make(AddressIdentityIssuerV1::class),
                    $app->make(BusinessYearAuthorityV1::class),
                    $app->make(PromotionCommandLedger::class),
                    $app->make(PromotionTransaction::class),
                );
            },
        );
        $this->app->alias(DeterministicPromoteAuthoredPropertyV1::class, PromoteAuthoredPropertyV1::class);
    }
}
