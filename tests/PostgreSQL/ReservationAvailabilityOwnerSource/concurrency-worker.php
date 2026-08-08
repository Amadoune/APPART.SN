<?php

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ReservationAvailabilityOwnerSource\ReservationAvailabilityOwnerSourceMapperTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1] ?? throw new RuntimeException('Missing barrier.');
$worker = $argv[2] ?? throw new RuntimeException('Missing worker number.');
$mode = $argv[3] ?? 'proposed';
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
if (! is_file($barrier.'.start')) {
    throw new RuntimeException('Concurrency barrier timed out.');
}

$connection = PostgreSqlTestEnvironment::connection();
$source = new PostgreSqlReservationAvailabilityOwnerSource($connection, new ReservationAvailabilityOwnerSourceMapper);
echo $source->append(
    $mode === 'hold'
        ? ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '09:00', (int) $worker)
        : ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00'),
)->value;
