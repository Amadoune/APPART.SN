<?php

namespace Tests\Feature;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeV1;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use PDO;
use Tests\TestCase;

final class ReservationAvailabilityOwnerSourceRuntimeCompositionTest extends TestCase
{
    public function test_runtime_bindings_are_lazy_unique_singletons(): void
    {
        self::assertFalse($this->app->resolved(ReservationAvailabilityOwnerSourceRuntimeV1::class));
        $adapter = new PostgreSqlReservationAvailabilityOwnerSource(
            new PDO('sqlite::memory:'),
            new ReservationAvailabilityOwnerSourceMapper,
        );
        $this->app->instance(PostgreSqlReservationAvailabilityOwnerSource::class, $adapter);

        self::assertTrue($this->app->bound(ReservationAvailabilityOwnerSource::class));
        self::assertTrue($this->app->bound(ReservationAvailabilityOwnerSourceRuntimeV1::class));

        $source = $this->app->make(ReservationAvailabilityOwnerSource::class);
        $runtime = $this->app->make(ReservationAvailabilityOwnerSourceRuntimeV1::class);

        self::assertSame($adapter, $source);
        self::assertSame($source, $this->app->make(ReservationAvailabilityOwnerSource::class));
        self::assertSame($runtime, $this->app->make(ReservationAvailabilityOwnerSourceRuntimeV1::class));
    }
}
