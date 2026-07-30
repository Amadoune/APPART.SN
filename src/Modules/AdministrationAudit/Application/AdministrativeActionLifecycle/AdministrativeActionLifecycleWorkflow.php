<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionRecordingDisposition;

final readonly class AdministrativeActionLifecycleWorkflow
{
    public function decide(
        AdministrativeActionLifecycleState $state,
        AdministrativeActionLifecycleAction $action,
        AdministrativeActionDecisionContext $decisionContext,
    ): AdministrativeActionLifecycleDecision {
        if ($action === AdministrativeActionLifecycleAction::Unknown) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::UnknownAction,
            );
        }

        if ($this->isTerminal($state)) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::TerminalState,
            );
        }

        if ($state === AdministrativeActionLifecycleState::Draft) {
            return $this->decideFromDraft($action, $decisionContext);
        }

        return $this->decideFromPendingApproval($action, $decisionContext);
    }

    private function decideFromDraft(
        AdministrativeActionLifecycleAction $action,
        AdministrativeActionDecisionContext $context,
    ): AdministrativeActionLifecycleDecision {
        if ($action !== AdministrativeActionLifecycleAction::Record) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::IncompatibleState,
            );
        }

        if ($context->reasonEvidence === AdministrativeActionReasonEvidence::Missing) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::MissingReason,
            );
        }

        $to = match ($context->authority->disposition) {
            AdministrativeActionRecordingDisposition::DirectRecording => AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionRecordingDisposition::IndependentApprovalRequired => AdministrativeActionLifecycleState::PendingApproval,
        };

        return $this->allowed(AdministrativeActionLifecycleState::Draft, $to, $action);
    }

    private function decideFromPendingApproval(
        AdministrativeActionLifecycleAction $action,
        AdministrativeActionDecisionContext $context,
    ): AdministrativeActionLifecycleDecision {
        if ($action === AdministrativeActionLifecycleAction::Record) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::IncompatibleState,
            );
        }

        if ($context->reasonEvidence === AdministrativeActionReasonEvidence::Missing) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::MissingReason,
            );
        }

        if ($context->authority->disposition !== AdministrativeActionRecordingDisposition::IndependentApprovalRequired) {
            return AdministrativeActionLifecycleDecision::denied(
                AdministrativeActionLifecycleDiagnostic::ApprovalNotRequired,
            );
        }

        if ($action === AdministrativeActionLifecycleAction::Approve) {
            return $this->allowed(
                AdministrativeActionLifecycleState::PendingApproval,
                AdministrativeActionLifecycleState::Approved,
                $action,
            );
        }

        return $this->allowed(
            AdministrativeActionLifecycleState::PendingApproval,
            AdministrativeActionLifecycleState::Rejected,
            $action,
        );
    }

    private function isTerminal(AdministrativeActionLifecycleState $state): bool
    {
        return match ($state) {
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleState::Approved,
            AdministrativeActionLifecycleState::Rejected => true,
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::PendingApproval => false,
        };
    }

    private function allowed(
        AdministrativeActionLifecycleState $from,
        AdministrativeActionLifecycleState $to,
        AdministrativeActionLifecycleAction $action,
    ): AdministrativeActionLifecycleDecision {
        return AdministrativeActionLifecycleDecision::allowed(
            new AdministrativeActionLifecycleTransition($from, $to, $action),
        );
    }
}
