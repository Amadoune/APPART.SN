<?php

namespace Tests\Feature;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class ReservationAvailabilityOwnerSourceRuntimeReadCompositionTest extends TestCase
{
    public function test_runtime_read_binding_is_lazy_unique_and_singleton(): void
    {
        self::assertFalse($this->app->resolved(ReservationAvailabilityOwnerSourceRuntimeReadV1::class));
        $adapter = new PostgreSqlReservationAvailabilityOwnerSource(
            new PDO('sqlite::memory:'),
            new ReservationAvailabilityOwnerSourceMapper,
        );
        $this->app->instance(PostgreSqlReservationAvailabilityOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(ReservationAvailabilityOwnerSource::class));
        self::assertTrue($this->app->bound(ReservationAvailabilityOwnerSourceRuntimeReadV1::class));

        $runtimeRead = $this->app->make(ReservationAvailabilityOwnerSourceRuntimeReadV1::class);

        self::assertSame($runtimeRead, $this->app->make(ReservationAvailabilityOwnerSourceRuntimeReadV1::class));
        self::assertSame($adapter, $this->app->make(ReservationAvailabilityOwnerSource::class));
    }
}
