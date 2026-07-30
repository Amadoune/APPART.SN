<?php

use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
touch($barrier.'.ready.'.$argv[2]);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$mapper = new ProfessionalStatusWorkflowMapper;
$contextMapper = new ProfessionalStatusContextMapper;
$historical = new PostgreSqlProfessionalStatusWorkflowRepository($pdo, $mapper);
$store = new PostgreSqlProfessionalStatusContextualTransitionRepository($pdo, $historical, $mapper, $contextMapper);
$inspector = new PostgreSqlProfessionalStatusContextualReplayInspector($pdo, $mapper, $contextMapper);
$orchestrator = new DeterministicProfessionalStatusOrchestrator($store, $inspector, new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);
$integrator = new ProfessionalStatusAtomicEventOrchestrator($orchestrator, $inspector, new PostgreSqlAggregateOutboxTransaction($pdo), new ProfessionalStatusEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), new PostgreSqlPublicProjectionOutboxWriter($pdo, new PostgreSqlPublicProjectionOutboxMapper), PublicProjectionOutboxConsumerId::fromString('public-projection-updater'));
$occurred = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:00Z'));
$request = new ProfessionalStatusAtomicEventRequest(ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000402'), ProfessionalStatusAction::Suspend, new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $occurred, new ProfessionalStatusExpectedVersion(1)), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:01Z')));
echo $integrator->transition($request)->status->value;
