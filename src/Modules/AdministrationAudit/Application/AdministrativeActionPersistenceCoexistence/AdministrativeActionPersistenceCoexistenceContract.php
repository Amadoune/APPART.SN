<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

final readonly class AdministrativeActionPersistenceCoexistenceContract
{
    /** @return list<AdministrativeActionPersistenceCoexistenceRule> */
    public function rules(): array
    {
        return [
            $this->rule(
                AdministrativeActionPersistenceOperation::Creation,
                AdministrativeActionPersistenceOwner::HistoricalRegistry,
                AdministrativeActionPersistenceWriteMode::HistoricalOnly,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::ReasonMutation,
                AdministrativeActionPersistenceOwner::HistoricalRegistry,
                AdministrativeActionPersistenceWriteMode::HistoricalOnly,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::AuditDetailMutation,
                AdministrativeActionPersistenceOwner::HistoricalRegistry,
                AdministrativeActionPersistenceWriteMode::HistoricalOnly,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::LifecycleEnrollment,
                AdministrativeActionPersistenceOwner::LifecycleJournal,
                AdministrativeActionPersistenceWriteMode::LifecycleOnly,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::LifecycleRead,
                AdministrativeActionPersistenceOwner::LifecycleJournal,
                AdministrativeActionPersistenceWriteMode::ReadOnly,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::LifecycleTransition,
                AdministrativeActionPersistenceOwner::LifecycleJournal,
                AdministrativeActionPersistenceWriteMode::AtomicHistoricalAndLifecycle,
            ),
            $this->rule(
                AdministrativeActionPersistenceOperation::CompatibilityRead,
                AdministrativeActionPersistenceOwner::HistoricalRegistry,
                AdministrativeActionPersistenceWriteMode::ReadOnly,
            ),
        ];
    }

    public function ruleFor(
        AdministrativeActionPersistenceOperation $operation,
    ): AdministrativeActionPersistenceCoexistenceRule {
        return match ($operation) {
            AdministrativeActionPersistenceOperation::Creation => $this->rules()[0],
            AdministrativeActionPersistenceOperation::ReasonMutation => $this->rules()[1],
            AdministrativeActionPersistenceOperation::AuditDetailMutation => $this->rules()[2],
            AdministrativeActionPersistenceOperation::LifecycleEnrollment => $this->rules()[3],
            AdministrativeActionPersistenceOperation::LifecycleRead => $this->rules()[4],
            AdministrativeActionPersistenceOperation::LifecycleTransition => $this->rules()[5],
            AdministrativeActionPersistenceOperation::CompatibilityRead => $this->rules()[6],
        };
    }

    private function rule(
        AdministrativeActionPersistenceOperation $operation,
        AdministrativeActionPersistenceOwner $owner,
        AdministrativeActionPersistenceWriteMode $writeMode,
    ): AdministrativeActionPersistenceCoexistenceRule {
        return new AdministrativeActionPersistenceCoexistenceRule($operation, $owner, $writeMode, false);
    }
}
