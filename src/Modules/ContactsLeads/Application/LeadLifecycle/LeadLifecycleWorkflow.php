<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

final readonly class LeadLifecycleWorkflow
{
    public function initialState(): LeadLifecycleState
    {
        return LeadLifecycleState::Created;
    }

    public function decide(LeadLifecycleState $state, LeadLifecycleAction $action): LeadLifecycleDecision
    {
        return match ($state->value.'>'.$action->value) {
            'created>deliver' => $this->allowed($state, LeadLifecycleState::Delivered, $action),
            'created>reject' => $this->allowed($state, LeadLifecycleState::Rejected, $action),
            'delivered>close', 'rejected>close' => $this->allowed($state, LeadLifecycleState::Closed, $action),

            'created>close' => LeadLifecycleDecision::denied(LeadLifecycleDiagnostic::TransitionForbidden),
            'delivered>deliver', 'delivered>reject',
            'rejected>deliver', 'rejected>reject' => LeadLifecycleDecision::denied(LeadLifecycleDiagnostic::IncompatibleState),
            'closed>deliver', 'closed>reject', 'closed>close' => LeadLifecycleDecision::denied(LeadLifecycleDiagnostic::TerminalState),
            'created>unknown', 'delivered>unknown',
            'rejected>unknown', 'closed>unknown' => LeadLifecycleDecision::denied(LeadLifecycleDiagnostic::UnknownAction),
        };
    }

    private function allowed(
        LeadLifecycleState $from,
        LeadLifecycleState $to,
        LeadLifecycleAction $action,
    ): LeadLifecycleDecision {
        return LeadLifecycleDecision::allowed(new LeadLifecycleTransition($from, $to, $action));
    }
}
