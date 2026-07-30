<?php

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';
[$script,$barrier,$number,$action,$actor] = $argv;
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$mapper = new LeadLifecycleWorkflowMapper;
$context = new LeadLifecycleContextMapper;
$historical = new PostgreSqlLeadLifecycleWorkflowRepository($pdo, $mapper);
$store = new PostgreSqlLeadLifecycleContextualTransitionRepository($pdo, $historical, $mapper, $context);
$orchestrator = new DeterministicLeadLifecycleOrchestrator($store, new PostgreSqlLeadLifecycleContextualReplayInspector($pdo, $mapper, $context), new LeadLifecycleWorkflow);
$id = LeadId::fromString('a4100000-0000-4000-8000-000000000097');
echo $orchestrator->execute(new LeadLifecycleTransitionRequest($id, LeadLifecycleAction::from($action), 1, new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')))))->status->value;
