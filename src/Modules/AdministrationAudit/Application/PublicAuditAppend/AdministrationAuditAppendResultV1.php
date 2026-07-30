<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

enum AdministrationAuditAppendResultV1: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentRecord = 'divergent_record';
    case Rejected = 'rejected';
    case DependencyUnavailable = 'dependency_unavailable';
}
