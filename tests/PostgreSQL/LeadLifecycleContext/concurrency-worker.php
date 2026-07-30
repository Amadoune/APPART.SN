<?php

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';
[$script,$barrier,$number,$actor] = $argv;
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$old = new PostgreSqlLeadLifecycleWorkflowRepository($pdo, new LeadLifecycleWorkflowMapper);
$repo = new PostgreSqlLeadLifecycleContextualTransitionRepository($pdo, $old, new LeadLifecycleWorkflowMapper, new LeadLifecycleContextMapper);
$id = LeadId::fromString('a4100000-0000-4000-8000-000000000092');
echo $repo->append(new LeadLifecycleContextualAppend($id, new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver), 1, new LeadLifecycleTransitionContext(LeadLifecycleActorId::fromString($actor), LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:00+00:00')))))->value;
