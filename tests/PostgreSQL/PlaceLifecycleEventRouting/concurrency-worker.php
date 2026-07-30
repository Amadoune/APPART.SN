<?php

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use App\Infrastructure\PlaceLifecycleEventRouting\PostgreSql\PostgreSqlPlaceLifecycleInboxRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\Geography\DurablePlaceLifecycleEventRouterTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    throw new RuntimeException('The Place routing worker requires a barrier and worker number.');
}

[, $barrier, $worker] = $arguments;
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$repository = new PostgreSqlPlaceLifecycleInboxRepository(
    PostgreSqlTestEnvironment::connection(),
    new PlaceLifecycleTransportSerializer,
);

echo $repository->store(DurablePlaceLifecycleEventRouterTest::envelope())->status->value;
