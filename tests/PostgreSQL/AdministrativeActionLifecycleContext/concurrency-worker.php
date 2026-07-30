<?php

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleTransitionRequest;
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
$lifecycle = new PostgreSqlAdministrativeActionLifecycleRepository($pdo, new AdministrativeActionLifecycleWorkflowMapper, new AdministrativeActionEnrollmentCanonicalizer);
$store = new PostgreSqlAdministrativeActionContextualTransitionRepository($pdo, $lifecycle, new AdministrativeActionTransitionContextMapper);
$inspector = new PostgreSqlAdministrativeActionContextualReplayInspector($pdo, new AdministrativeActionLifecycleWorkflowMapper);
$orchestrator = new DeterministicAdministrativeActionLifecycleOrchestrator($store, $inspector, new AdministrativeActionReplayPolicy, new AdministrativeActionLifecycleWorkflow);
$actor = ActorId::fromString('author-001');
$decision = new AdministrativeActionDecisionContext(AdministrativeActionDecisionContextVersion::V1, AdministrativeActionReasonEvidence::Present, AdministrativeActionDecisionAuthority::directRecording($actor, $actor));
$id = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000035');
echo $orchestrator->execute(new AdministrativeActionLifecycleTransitionRequest(
    $id,
    AdministrativeActionLifecycleAction::Record,
    AdministrativeActionTransitionExecutionContext::record(
        new AdministrativeActionExpectedVersion(1),
        $actor,
        AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:00.000000+00:00')),
        AuditReason::fromString('Administrative contextual persistence reason.'),
        $decision,
    ),
))->status->value;
