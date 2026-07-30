<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

enum AdministrativeActionLifecycleEventType: string
{
    case Recorded = 'administrative.action.lifecycle.recorded';
    case ApprovalRequested = 'administrative.action.lifecycle.approval_requested';
    case Approved = 'administrative.action.lifecycle.approved';
    case Rejected = 'administrative.action.lifecycle.rejected';
}
