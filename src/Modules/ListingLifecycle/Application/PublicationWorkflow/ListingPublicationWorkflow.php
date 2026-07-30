<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

final readonly class ListingPublicationWorkflow
{
    /** @var array<string, ListingPublicationState> */
    private const array TRANSITIONS = [
        'draft>submit' => ListingPublicationState::Submitted,
        'draft>withdraw' => ListingPublicationState::Withdrawn,
        'draft>archive' => ListingPublicationState::Archived,
        'submitted>begin_review' => ListingPublicationState::UnderReview,
        'submitted>withdraw' => ListingPublicationState::Withdrawn,
        'under_review>approve_and_publish' => ListingPublicationState::Published,
        'under_review>request_changes' => ListingPublicationState::ChangesRequested,
        'under_review>reject' => ListingPublicationState::Rejected,
        'under_review>withdraw' => ListingPublicationState::Withdrawn,
        'changes_requested>submit' => ListingPublicationState::Submitted,
        'changes_requested>withdraw' => ListingPublicationState::Withdrawn,
        'changes_requested>archive' => ListingPublicationState::Archived,
        'published>review_material_change' => ListingPublicationState::UnderReview,
        'published>suspend' => ListingPublicationState::Suspended,
        'published>expire' => ListingPublicationState::Expired,
        'published>withdraw' => ListingPublicationState::Withdrawn,
        'suspended>reinstate' => ListingPublicationState::Published,
        'suspended>request_changes' => ListingPublicationState::ChangesRequested,
        'suspended>reject' => ListingPublicationState::Rejected,
        'suspended>archive' => ListingPublicationState::Archived,
        'expired>review_renewal' => ListingPublicationState::UnderReview,
        'expired>renew_directly' => ListingPublicationState::Published,
        'expired>withdraw' => ListingPublicationState::Withdrawn,
        'expired>archive' => ListingPublicationState::Archived,
        'withdrawn>approve_republication' => ListingPublicationState::UnderReview,
        'withdrawn>archive' => ListingPublicationState::Archived,
        'rejected>archive' => ListingPublicationState::Archived,
    ];

    public function decide(ListingPublicationState $state, ListingPublicationAction $action): ListingPublicationDecision
    {
        if ($action === ListingPublicationAction::Unknown) {
            return ListingPublicationDecision::denied(ListingPublicationDiagnostic::unknownAction());
        }
        if ($state === ListingPublicationState::Archived) {
            return ListingPublicationDecision::denied(ListingPublicationDiagnostic::terminalState());
        }

        $target = self::TRANSITIONS[$state->value.'>'.$action->value] ?? null;
        if ($target !== null) {
            return ListingPublicationDecision::allowed(new ListingPublicationTransition($state, $target, $action));
        }
        if ($this->nominalTarget($action) === $state) {
            return ListingPublicationDecision::denied(ListingPublicationDiagnostic::incompatibleState());
        }

        return ListingPublicationDecision::denied(ListingPublicationDiagnostic::transitionForbidden());
    }

    private function nominalTarget(ListingPublicationAction $action): ListingPublicationState
    {
        return match ($action) {
            ListingPublicationAction::Submit => ListingPublicationState::Submitted,
            ListingPublicationAction::BeginReview,
            ListingPublicationAction::ReviewMaterialChange,
            ListingPublicationAction::ReviewRenewal,
            ListingPublicationAction::ApproveRepublication => ListingPublicationState::UnderReview,
            ListingPublicationAction::ApproveAndPublish,
            ListingPublicationAction::Reinstate,
            ListingPublicationAction::RenewDirectly => ListingPublicationState::Published,
            ListingPublicationAction::RequestChanges => ListingPublicationState::ChangesRequested,
            ListingPublicationAction::Reject => ListingPublicationState::Rejected,
            ListingPublicationAction::Withdraw => ListingPublicationState::Withdrawn,
            ListingPublicationAction::Suspend => ListingPublicationState::Suspended,
            ListingPublicationAction::Expire => ListingPublicationState::Expired,
            ListingPublicationAction::Archive => ListingPublicationState::Archived,
            ListingPublicationAction::Unknown => throw new \LogicException('Unknown action has no nominal target.'),
        };
    }
}
