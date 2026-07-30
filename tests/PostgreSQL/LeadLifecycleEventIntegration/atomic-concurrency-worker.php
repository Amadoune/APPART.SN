<?php

use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $number] = $argv;
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$mapper = new LeadLifecycleWorkflowMapper;
$context = new LeadLifecycleContextMapper;
$historical = new PostgreSqlLeadLifecycleWorkflowRepository($pdo, $mapper);
$store = new PostgreSqlLeadLifecycleContextualTransitionRepository($pdo, $historical, $mapper, $context);
$inspector = new PostgreSqlLeadLifecycleContextualReplayInspector($pdo, $mapper, $context);
$integrator = new LeadLifecycleAtomicEventOrchestrator(
    new DeterministicLeadLifecycleOrchestrator($store, $inspector, new LeadLifecycleWorkflow),
    $inspector,
    new PostgreSqlAggregateOutboxTransaction($pdo),
    new LeadLifecycleEventCatalog,
    new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
    new PostgreSqlPublicProjectionOutboxWriter($pdo, new PostgreSqlPublicProjectionOutboxMapper),
    PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
);
echo $integrator->transition(new LeadLifecycleAtomicEventRequest(
    LeadId::fromString('a4400000-0000-4000-8000-000000000402'),
    LeadLifecycleAction::Deliver,
    1,
    LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
    LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:00Z')),
    LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:01Z')),
))->status->value;
