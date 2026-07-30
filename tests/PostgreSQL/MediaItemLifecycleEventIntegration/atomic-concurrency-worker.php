<?php

use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $number] = $argv;
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$pdo = PostgreSqlTestEnvironment::connection();
$mapper = new MediaItemLifecycleWorkflowMapper;
$contextMapper = new MediaItemLifecycleContextMapper;
$historical = new PostgreSqlMediaItemLifecycleWorkflowRepository($pdo, $mapper);
$store = new PostgreSqlMediaItemLifecycleContextualTransitionRepository($pdo, $historical, $mapper, $contextMapper);
$inspector = new PostgreSqlMediaItemLifecycleContextualReplayInspector($pdo, $mapper);
$orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, $inspector, new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);
$integrator = new MediaItemLifecycleAtomicEventOrchestrator($orchestrator, $inspector, new PostgreSqlAggregateOutboxTransaction($pdo), new MediaItemLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), new PostgreSqlPublicProjectionOutboxWriter($pdo, new PostgreSqlPublicProjectionOutboxMapper), PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
$id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000402');
$occurred = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:00Z'));
$context = new MediaItemLifecycleTransitionContext(MediaItemLifecycleContextVersion::V1, MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'), MediaId::fromString($id->value), new MediaItemLifecycleExpectedVersion(1), new MediaCollectionDecisionVersion(7), MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $occurred, MediaCollectionTransitionDecision::notPrimary());
$request = new MediaItemLifecycleAtomicEventRequest($id, MediaItemLifecycleAction::Remove, $context, MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:01Z')));

echo $integrator->transition($request)->status->value;
