<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

enum AdministrationAuditOutcomeV1: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Rejected = 'rejected';
    case Forbidden = 'forbidden';
    case Conflict = 'conflict';
    case DependencyUnavailable = 'dependency_unavailable';
}
