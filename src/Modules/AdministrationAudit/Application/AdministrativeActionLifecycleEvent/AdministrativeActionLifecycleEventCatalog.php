<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use DomainException;

final readonly class AdministrativeActionLifecycleEventCatalog
{
    public function typeFor(AdministrativeActionLifecycleTransition $transition): AdministrativeActionLifecycleEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleAction::Record, AdministrativeActionLifecycleState::Recorded] => AdministrativeActionLifecycleEventType::Recorded,
            [AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleAction::Record, AdministrativeActionLifecycleState::PendingApproval] => AdministrativeActionLifecycleEventType::ApprovalRequested,
            [AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleAction::Approve, AdministrativeActionLifecycleState::Approved] => AdministrativeActionLifecycleEventType::Approved,
            [AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleAction::Reject, AdministrativeActionLifecycleState::Rejected] => AdministrativeActionLifecycleEventType::Rejected,
            default => throw new DomainException('Transition is not certified for an Administrative Action Lifecycle event.'),
        };
    }
}
