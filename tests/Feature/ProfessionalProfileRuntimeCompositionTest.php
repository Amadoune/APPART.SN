<?php

namespace Tests\Feature;

use App\Application\ProfessionalProfileRuntime\Contract\ProfessionalProfileRuntimeV1;
use App\Application\ProfessionalProfileRuntime\ProfessionalProfileRuntimeStatus;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract\ProfessionalVerificationStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicPortfolioStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalPublicProfileStore;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalVerificationStore;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class ProfessionalProfileRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_owner_bindings_are_lazy_singletons_on_the_shared_connection(): void
    {
        foreach ([ProfessionalPublicProfileStore::class, ProfessionalVerificationStore::class, ProfessionalPublicPortfolioStore::class] as $contract) {
            self::assertFalse($this->app->resolved($contract));
        }

        $runtime = $this->app->make(ProfessionalProfileRuntimeV1::class);
        $stores = [
            [$runtime->publicProfile(), PostgreSqlProfessionalPublicProfileStore::class],
            [$runtime->verification(), PostgreSqlProfessionalVerificationStore::class],
            [$runtime->publicPortfolio(), PostgreSqlProfessionalPublicPortfolioStore::class],
        ];
        foreach ($stores as [$store, $class]) {
            self::assertInstanceOf($class, $store);
            self::assertSame($this->app->make(PDO::class), (new ReflectionProperty($store, 'connection'))->getValue($store));
        }

        self::assertSame(ProfessionalProfileRuntimeStatus::Ready, $runtime->inspect()->status);
        self::assertSame($runtime, $this->app->make(ProfessionalProfileRuntimeV1::class));
        self::assertFalse($this->app->make(PDO::class)->inTransaction());
    }
}
