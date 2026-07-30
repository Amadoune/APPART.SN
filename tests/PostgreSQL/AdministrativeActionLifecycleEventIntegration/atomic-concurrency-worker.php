<?php

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\DeterministicAdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransitionContextMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualTransitionRepository;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[, $barrier, $worker] = $_SERVER['argv'];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$pdo = PostgreSqlTestEnvironment::connection();
$workflowMapper = new AdministrativeActionLifecycleWorkflowMapper;
$lifecycle = new PostgreSqlAdministrativeActionLifecycleRepository($pdo, $workflowMapper, new AdministrativeActionEnrollmentCanonicalizer);
$store = new PostgreSqlAdministrativeActionContextualTransitionRepository($pdo, $lifecycle, new AdministrativeActionTransitionContextMapper);
$inspector = new PostgreSqlAdministrativeActionContextualReplayInspector($pdo, $workflowMapper);
$orchestrator = new DeterministicAdministrativeActionLifecycleOrchestrator($store, $inspector, new AdministrativeActionReplayPolicy, new AdministrativeActionLifecycleWorkflow);
$integrator = new AdministrativeActionLifecycleAtomicEventOrchestrator(
    $orchestrator,
    $inspector,
    new PostgreSqlAggregateOutboxTransaction($pdo),
    new AdministrativeActionLifecycleEventCatalog,
    new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
    new PostgreSqlPublicProjectionOutboxWriter($pdo, new PostgreSqlPublicProjectionOutboxMapper),
    PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
);
$actor = ActorId::fromString('author-001');
$decision = new AdministrativeActionDecisionContext(
    AdministrativeActionDecisionContextVersion::V1,
    AdministrativeActionReasonEvidence::Present,
    AdministrativeActionDecisionAuthority::directRecording($actor, $actor),
);
$occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:00:00Z'));
$request = new AdministrativeActionLifecycleAtomicEventRequest(
    AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000471'),
    AdministrativeActionLifecycleAction::Record,
    AdministrativeActionTransitionExecutionContext::record(
        new AdministrativeActionExpectedVersion(1),
        $actor,
        $occurredAt,
        AuditReason::fromString('Administrative atomic integration reason.'),
        $decision,
    ),
    AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:00:01Z')),
);

echo $integrator->transition($request)->status->value;
