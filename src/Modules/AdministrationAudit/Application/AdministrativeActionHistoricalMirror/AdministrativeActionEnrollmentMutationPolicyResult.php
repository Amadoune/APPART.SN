<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

enum AdministrativeActionEnrollmentMutationPolicyResult: string
{
    case AllowedBeforeEnrollment = 'allowed_before_enrollment';
    case MutationRejected = 'mutation_rejected';
    case LifecycleAuthorityRequired = 'lifecycle_authority_required';
    case CompatibilityReadAllowed = 'compatibility_read_allowed';
}
