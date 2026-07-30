<?php

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventRequest;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\DeterministicReservationLifecycleEventOrchestrator;
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
$connection = PostgreSqlTestEnvironment::connection();
$store = new PostgreSqlReservationLifecycleWorkflowRepository($connection, new ReservationLifecycleWorkflowMapper);
$orchestrator = new ReservationLifecycleAtomicEventOrchestrator(
    new DeterministicReservationLifecycleEventOrchestrator(new ReservationLifecycleWorkflow, $store),
    new PostgreSqlAggregateOutboxTransaction($connection),
    new ReservationLifecycleEventCatalog,
    new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
    new PostgreSqlPublicProjectionOutboxWriter($connection, new PostgreSqlPublicProjectionOutboxMapper),
    PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
);
echo $orchestrator->transition(new ReservationLifecycleAtomicEventRequest(
    ReservationId::fromString('22222222-2222-4222-8222-222222222222'),
    ReservationLifecycleAction::Submit,
    1,
    new DateTimeImmutable('2026-07-21T10:00:00.000000Z'),
    new DateTimeImmutable('2026-07-21T10:00:01.000000Z'),
))->status->value;
