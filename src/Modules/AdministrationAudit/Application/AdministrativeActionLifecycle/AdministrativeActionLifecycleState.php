<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

enum AdministrativeActionLifecycleState: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Recorded = 'recorded';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
