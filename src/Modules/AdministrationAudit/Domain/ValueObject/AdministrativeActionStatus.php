<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

enum AdministrativeActionStatus: string
{
    case Draft = 'draft';
    case Recorded = 'recorded';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
