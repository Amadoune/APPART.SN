<?php

namespace Tests\PostgreSQL\LeadLifecycleContext;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\LeadLifecycleContextualTransitionStoreContract;

final class PostgreSqlLeadLifecycleContextualTransitionContractTest extends LeadLifecycleContextualTransitionStoreContract
{
    private PostgreSqlLeadLifecycleWorkflowRepository $historical;

    protected function setUp(): void
    {
        $pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($pdo);
        PostgreSqlTestEnvironment::reset($pdo);
        $this->historical = new PostgreSqlLeadLifecycleWorkflowRepository($pdo, new LeadLifecycleWorkflowMapper);
        $this->store = new PostgreSqlLeadLifecycleContextualTransitionRepository($pdo, $this->historical, new LeadLifecycleWorkflowMapper, new LeadLifecycleContextMapper);
    }

    private LeadLifecycleContextualTransitionStore $store;

    protected function contextualStore(): LeadLifecycleContextualTransitionStore
    {
        return $this->store;
    }

    protected function seedCreated(LeadId $leadId): void
    {
        $this->historical->initialize($leadId, LeadLifecycleState::Created);
    }
}
