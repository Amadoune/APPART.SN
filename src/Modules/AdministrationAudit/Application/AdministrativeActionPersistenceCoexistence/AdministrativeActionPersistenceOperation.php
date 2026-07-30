<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

enum AdministrativeActionPersistenceOperation: string
{
    case Creation = 'creation';
    case ReasonMutation = 'reason_mutation';
    case AuditDetailMutation = 'audit_detail_mutation';
    case LifecycleEnrollment = 'lifecycle_enrollment';
    case LifecycleRead = 'lifecycle_read';
    case LifecycleTransition = 'lifecycle_transition';
    case CompatibilityRead = 'compatibility_read';
}
