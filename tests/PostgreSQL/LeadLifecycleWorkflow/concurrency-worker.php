<?php

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$repository = new PostgreSqlLeadLifecycleWorkflowRepository(
    PostgreSqlTestEnvironment::connection(),
    new LeadLifecycleWorkflowMapper,
);
echo $repository->initialize(
    LeadId::fromString('a4100000-0000-4000-8000-000000000099'),
    LeadLifecycleState::Created,
)->value;
