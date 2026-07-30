<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountClosureStateReader;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicAccountAvailabilityInspector implements AccountAvailabilityInspector
{
    public function __construct(
        private AccountRegistry $accounts,
        private AccountStatusWorkflowStore $statuses,
        private AccountClosureStateReader $closures,
    ) {}

    public function inspect(
        AccountId $accountId,
        AccountAvailabilityPurpose $purpose,
        DateTimeImmutable $observedAt,
    ): AccountAvailabilityResult {
        try {
            $account = $this->accounts->find($accountId);
            $status = $this->statuses->read($accountId);
            $closure = $this->closures->read($accountId);
        } catch (Throwable) {
            return $this->result($accountId, $purpose, $observedAt, AccountAvailabilityStatus::Indeterminate);
        }

        if ($status->status === AccountStatusPersistenceReadStatus::PersistenceRejected
            || $closure->status === AccountClosureReadStatus::PersistenceRejected) {
            return $this->result($accountId, $purpose, $observedAt, AccountAvailabilityStatus::Indeterminate);
        }

        $statusState = $status->snapshot === null
            ? $status->legacyState
            : $status->snapshot->state;
        $statusVersion = $status->snapshot === null
            ? ($status->status === AccountStatusPersistenceReadStatus::LegacyUninitialized ? 0 : null)
            : $status->snapshot->version->value;
        $closureState = $closure->state;

        if ($account === null) {
            $availability = $status->status === AccountStatusPersistenceReadStatus::AccountMissing
                && $closure->status === AccountClosureReadStatus::LegacyOpen
                ? AccountAvailabilityStatus::AccountMissing
                : AccountAvailabilityStatus::Inconsistent;

            return $this->result($accountId, $purpose, $observedAt, $availability, $statusState, $statusVersion, $closureState, $closure->version);
        }

        if ($status->status === AccountStatusPersistenceReadStatus::AccountMissing
            || $statusState === null || $closureState === null) {
            return $this->result(
                $accountId,
                $purpose,
                $observedAt,
                AccountAvailabilityStatus::Inconsistent,
                $statusState,
                $statusVersion,
                $closureState,
                $closure->version,
            );
        }

        if ($statusState === AccountStatusState::Suspended) {
            return $this->result($accountId, $purpose, $observedAt, AccountAvailabilityStatus::UnavailableSuspended, $statusState, $statusVersion, $closureState, $closure->version);
        }

        if ($purpose === AccountAvailabilityPurpose::ReopenClosure) {
            $availability = $closureState === AccountClosureState::Closed
                ? AccountAvailabilityStatus::Available
                : AccountAvailabilityStatus::Inconsistent;

            return $this->result($accountId, $purpose, $observedAt, $availability, $statusState, $statusVersion, $closureState, $closure->version);
        }

        if ($closureState === AccountClosureState::Closed
            || $closureState === AccountClosureState::ClosureRequested) {
            return $this->result($accountId, $purpose, $observedAt, AccountAvailabilityStatus::UnavailableClosed, $statusState, $statusVersion, $closureState, $closure->version);
        }

        return $this->result($accountId, $purpose, $observedAt, AccountAvailabilityStatus::Available, $statusState, $statusVersion, $closureState, $closure->version);
    }

    private function result(
        AccountId $accountId,
        AccountAvailabilityPurpose $purpose,
        DateTimeImmutable $observedAt,
        AccountAvailabilityStatus $status,
        ?AccountStatusState $accountStatus = null,
        ?int $accountStatusVersion = null,
        ?AccountClosureState $closureState = null,
        ?int $closureVersion = null,
    ): AccountAvailabilityResult {
        return new AccountAvailabilityResult(
            $accountId,
            $purpose,
            $status,
            $observedAt,
            $accountStatus,
            $accountStatusVersion,
            $closureState,
            $closureVersion,
        );
    }
}
