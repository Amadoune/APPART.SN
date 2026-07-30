<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

final readonly class ProfessionalStatusWorkflow
{
    public function initialState(): ProfessionalStatusState
    {
        return ProfessionalStatusState::Active;
    }

    public function decide(ProfessionalStatusState $state, ProfessionalStatusAction $action): ProfessionalStatusDecision
    {
        return match ($state->value.'>'.$action->value) {
            'active>suspend' => $this->allowed($state, ProfessionalStatusState::Suspended, $action),
            'suspended>reactivate' => $this->allowed($state, ProfessionalStatusState::Active, $action),
            'active>reactivate', 'suspended>suspend' => ProfessionalStatusDecision::denied(ProfessionalStatusDiagnostic::IncompatibleState),
            'active>unknown', 'suspended>unknown' => ProfessionalStatusDecision::denied(ProfessionalStatusDiagnostic::UnknownAction),
        };
    }

    private function allowed(ProfessionalStatusState $from, ProfessionalStatusState $to, ProfessionalStatusAction $action): ProfessionalStatusDecision
    {
        return ProfessionalStatusDecision::allowed(new ProfessionalStatusTransition($from, $to, $action));
    }
}
