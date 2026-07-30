<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionPersistenceOperation;

final readonly class AdministrativeActionEnrollmentMutationPolicy
{
    public function classify(
        AdministrativeActionPersistenceOperation $operation,
        bool $enrolled,
    ): AdministrativeActionEnrollmentMutationPolicyResult {
        if (! $enrolled) {
            return match ($operation) {
                AdministrativeActionPersistenceOperation::Creation,
                AdministrativeActionPersistenceOperation::ReasonMutation,
                AdministrativeActionPersistenceOperation::AuditDetailMutation => AdministrativeActionEnrollmentMutationPolicyResult::AllowedBeforeEnrollment,
                AdministrativeActionPersistenceOperation::CompatibilityRead => AdministrativeActionEnrollmentMutationPolicyResult::CompatibilityReadAllowed,
                AdministrativeActionPersistenceOperation::LifecycleEnrollment,
                AdministrativeActionPersistenceOperation::LifecycleRead,
                AdministrativeActionPersistenceOperation::LifecycleTransition => AdministrativeActionEnrollmentMutationPolicyResult::LifecycleAuthorityRequired,
            };
        }

        return match ($operation) {
            AdministrativeActionPersistenceOperation::CompatibilityRead => AdministrativeActionEnrollmentMutationPolicyResult::CompatibilityReadAllowed,
            AdministrativeActionPersistenceOperation::Creation,
            AdministrativeActionPersistenceOperation::ReasonMutation,
            AdministrativeActionPersistenceOperation::AuditDetailMutation,
            AdministrativeActionPersistenceOperation::LifecycleEnrollment => AdministrativeActionEnrollmentMutationPolicyResult::MutationRejected,
            AdministrativeActionPersistenceOperation::LifecycleRead,
            AdministrativeActionPersistenceOperation::LifecycleTransition => AdministrativeActionEnrollmentMutationPolicyResult::LifecycleAuthorityRequired,
        };
    }
}
