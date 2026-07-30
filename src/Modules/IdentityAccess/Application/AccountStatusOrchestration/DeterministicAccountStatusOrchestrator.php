<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusCurrentState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusDecision;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Throwable;

final readonly class DeterministicAccountStatusOrchestrator implements AccountStatusOrchestrator
{
    public function __construct(
        private AccountStatusWorkflowStore $store,
        private AccountStatusWorkflow $workflow,
        private AccountStatusOrchestrationTransaction $transaction,
    ) {}

    public function transition(AccountStatusContextV1 $context): AccountStatusOrchestrationResult
    {
        try {
            return $this->transaction->run(fn (): AccountStatusOrchestrationResult => $this->coordinate($context));
        } catch (Throwable) {
            return new AccountStatusOrchestrationResult(
                AccountStatusOrchestrationStatus::PersistenceCorrupted,
                null,
            );
        }
    }

    private function coordinate(AccountStatusContextV1 $context): AccountStatusOrchestrationResult
    {
        $read = $this->store->read($context->accountId);

        if ($read->status === AccountStatusPersistenceReadStatus::AccountMissing) {
            return new AccountStatusOrchestrationResult(AccountStatusOrchestrationStatus::AccountMissing, null);
        }
        if ($read->status === AccountStatusPersistenceReadStatus::PersistenceRejected) {
            return new AccountStatusOrchestrationResult(AccountStatusOrchestrationStatus::PersistenceCorrupted, null);
        }
        if ($read->status === AccountStatusPersistenceReadStatus::LegacyUninitialized) {
            $bootstrap = $this->store->bootstrap($context->accountId);
            if ($bootstrap !== AccountStatusPersistenceWriteResult::Applied) {
                return $this->writeResult($bootstrap, null);
            }
            $read = $this->store->read($context->accountId);
        }

        if ($read->status !== AccountStatusPersistenceReadStatus::Found || $read->snapshot === null) {
            return new AccountStatusOrchestrationResult(AccountStatusOrchestrationStatus::PersistenceCorrupted, null);
        }

        $current = new AccountStatusCurrentState(
            $read->snapshot->accountId,
            $read->snapshot->state,
            $read->snapshot->version,
        );
        $decision = $this->workflow->decide($current, $context->action, $context);

        if ($decision->decision === AccountStatusDecision::AlreadyInState) {
            return new AccountStatusOrchestrationResult(
                AccountStatusOrchestrationStatus::AlreadyInState,
                $decision->state,
            );
        }
        if ($decision->decision === AccountStatusDecision::InvalidContext || $decision->transition === null) {
            return new AccountStatusOrchestrationResult(
                AccountStatusOrchestrationStatus::InvalidContext,
                $decision->state,
            );
        }

        return $this->writeResult(
            $this->store->append($decision->transition, $context),
            $decision->state,
        );
    }

    private function writeResult(
        AccountStatusPersistenceWriteResult $result,
        ?AccountStatusState $state,
    ): AccountStatusOrchestrationResult {
        return new AccountStatusOrchestrationResult(
            match ($result) {
                AccountStatusPersistenceWriteResult::Applied => AccountStatusOrchestrationStatus::Applied,
                AccountStatusPersistenceWriteResult::AccountMissing => AccountStatusOrchestrationStatus::AccountMissing,
                AccountStatusPersistenceWriteResult::VersionConflict => AccountStatusOrchestrationStatus::VersionConflict,
                AccountStatusPersistenceWriteResult::PersistenceRejected => AccountStatusOrchestrationStatus::PersistenceRejected,
            },
            $state,
        );
    }
}
