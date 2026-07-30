<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflowResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use RuntimeException;

final readonly class DeterministicLeadLifecycleOrchestrator implements LeadLifecycleOrchestrator
{
    public function __construct(private LeadLifecycleContextualTransitionStore $store, private LeadLifecycleContextualReplayInspector $inspector, private LeadLifecycleWorkflow $workflow) {}

    public function execute(LeadLifecycleTransitionRequest $request): LeadLifecycleOrchestrationResult
    {
        $read = $this->store->read($request->leadId);
        if ($read->status === LeadLifecyclePersistenceReadStatus::Missing) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Missing);
        }
        if ($read->status === LeadLifecyclePersistenceReadStatus::Corrupted || $read->snapshot === null) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted);
        }
        if ($read->snapshot->version === $request->expectedVersion + 1) {
            return $this->replay($request);
        }
        if ($read->snapshot->version !== $request->expectedVersion) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::VersionConflict);
        }
        $decision = $this->workflow->decide($read->snapshot->state, $request->action);
        if ($decision->result === LeadLifecycleWorkflowResult::Denied) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Denied, $decision->diagnostic);
        }
        if ($decision->transition === null) {
            throw new RuntimeException('Allowed workflow decision has no transition.');
        }
        $write = $this->store->append(new LeadLifecycleContextualAppend($request->leadId, $decision->transition, $request->expectedVersion, $request->context));

        return new LeadLifecycleOrchestrationResult(match ($write) {
            LeadLifecycleContextualWriteResult::Applied => LeadLifecycleOrchestrationStatus::Applied,LeadLifecycleContextualWriteResult::AlreadyApplied => LeadLifecycleOrchestrationStatus::AlreadyApplied,LeadLifecycleContextualWriteResult::VersionConflict => LeadLifecycleOrchestrationStatus::VersionConflict,LeadLifecycleContextualWriteResult::StateConflict,LeadLifecycleContextualWriteResult::TransitionRejected => LeadLifecycleOrchestrationStatus::StateConflict,LeadLifecycleContextualWriteResult::ContextDivergence => LeadLifecycleOrchestrationStatus::ContextDivergence,LeadLifecycleContextualWriteResult::Corrupted => LeadLifecycleOrchestrationStatus::PersistenceCorrupted
        });
    }

    private function replay(LeadLifecycleTransitionRequest $request): LeadLifecycleOrchestrationResult
    {
        $inspection = $this->inspector->inspectLatest($request->leadId);
        if ($inspection->status === LeadLifecycleContextualInspectionStatus::Corrupted || $inspection->snapshot === null) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted);
        }
        if ($inspection->status === LeadLifecycleContextualInspectionStatus::Missing || $inspection->snapshot->version !== $request->expectedVersion + 1) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::VersionConflict);
        }
        if ($inspection->snapshot->transition->action !== $request->action) {
            return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::StateConflict);
        }
        $sameActor = $inspection->snapshot->context->actor->value === $request->context->actor->value;
        $sameInstant = $inspection->snapshot->context->occurredAt->value == $request->context->occurredAt->value;

        return new LeadLifecycleOrchestrationResult($sameActor && $sameInstant ? LeadLifecycleOrchestrationStatus::AlreadyApplied : LeadLifecycleOrchestrationStatus::ContextDivergence);
    }
}
