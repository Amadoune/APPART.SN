<?php

use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use App\Infrastructure\ReservationLifecycleEventRouting\PostgreSql\PostgreSqlReservationLifecycleInboxRepository;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$event = (new ReservationLifecycleEventCatalog)->eventFor(
    ReservationId::fromString('22222222-2222-4222-8222-222222222222'),
    new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
    2,
);
$envelope = ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($event));
$repository = new PostgreSqlReservationLifecycleInboxRepository(PostgreSqlTestEnvironment::connection(), new ReservationLifecycleTransportSerializer);
echo $repository->store($envelope)->status->value;
