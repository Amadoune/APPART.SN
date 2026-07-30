<?php

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$repository = new PostgreSqlReservationLifecycleWorkflowRepository(PostgreSqlTestEnvironment::connection(), new ReservationLifecycleWorkflowMapper);
echo $repository->initialize(ReservationId::fromString('98100000-0000-4000-8000-000000000099'), ReservationLifecycleState::Draft)->value;
