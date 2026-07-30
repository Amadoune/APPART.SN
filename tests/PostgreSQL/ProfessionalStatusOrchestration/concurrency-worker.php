<?php

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;
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

[$script, $barrier, $number] = $argv;
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$workflowMapper = new ProfessionalStatusWorkflowMapper;
$contextMapper = new ProfessionalStatusContextMapper;
$historical = new PostgreSqlProfessionalStatusWorkflowRepository($pdo, $workflowMapper);
$store = new PostgreSqlProfessionalStatusContextualTransitionRepository($pdo, $historical, $workflowMapper, $contextMapper);
$inspector = new PostgreSqlProfessionalStatusContextualReplayInspector($pdo, $workflowMapper, $contextMapper);
$orchestrator = new DeterministicProfessionalStatusOrchestrator($store, $inspector, new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);
echo $orchestrator->execute(new ProfessionalStatusTransitionRequest(ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000046'), ProfessionalStatusAction::Suspend, new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')), new ProfessionalStatusExpectedVersion(1))))->status->value;
