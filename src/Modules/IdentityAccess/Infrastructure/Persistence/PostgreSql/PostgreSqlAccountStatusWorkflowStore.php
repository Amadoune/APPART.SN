<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlAccountStatusWorkflowStore implements AccountStatusWorkflowStore
{
    public function __construct(
        private PDO $connection,
        private AccountRegistry $accounts,
        private AccountStatusWorkflowMapper $mapper,
    ) {}

    public function read(AccountId $accountId): AccountStatusPersistenceReadResult
    {
        $account = $this->accounts->find($accountId);
        $row = $this->current($accountId, false);

        if ($account === null) {
            return $row === false
                ? AccountStatusPersistenceReadResult::accountMissing($accountId)
                : AccountStatusPersistenceReadResult::persistenceRejected($accountId);
        }

        if ($row === false) {
            return AccountStatusPersistenceReadResult::legacyUninitialized(
                $accountId,
                $account->isSuspended()
                    ? AccountStatusState::Suspended
                    : AccountStatusState::Active,
            );
        }

        try {
            return AccountStatusPersistenceReadResult::found($this->mapper->snapshot($row));
        } catch (Throwable) {
            return AccountStatusPersistenceReadResult::persistenceRejected($accountId);
        }
    }

    public function bootstrap(AccountId $accountId): AccountStatusPersistenceWriteResult
    {
        return $this->withinTransaction(function () use ($accountId): AccountStatusPersistenceWriteResult {
            $this->lock($accountId);
            $account = $this->accounts->find($accountId);
            $existing = $this->current($accountId, true);

            if ($account === null) {
                return $existing === false
                    ? AccountStatusPersistenceWriteResult::AccountMissing
                    : AccountStatusPersistenceWriteResult::PersistenceRejected;
            }

            if ($existing !== false) {
                try {
                    $this->mapper->snapshot($existing);

                    return AccountStatusPersistenceWriteResult::Applied;
                } catch (Throwable) {
                    return AccountStatusPersistenceWriteResult::PersistenceRejected;
                }
            }

            $this->insert($this->mapper->bootstrap($account));

            return AccountStatusPersistenceWriteResult::Applied;
        });
    }

    public function append(
        AccountStatusTransition $transition,
        AccountStatusContextV1 $context,
    ): AccountStatusPersistenceWriteResult {
        return $this->withinTransaction(function () use ($transition, $context): AccountStatusPersistenceWriteResult {
            $this->lock($context->accountId);
            if ($this->accounts->find($context->accountId) === null) {
                return AccountStatusPersistenceWriteResult::AccountMissing;
            }

            $current = $this->current($context->accountId, true);
            if ($current === false) {
                return AccountStatusPersistenceWriteResult::PersistenceRejected;
            }

            try {
                $snapshot = $this->mapper->snapshot($current);
            } catch (Throwable) {
                return AccountStatusPersistenceWriteResult::PersistenceRejected;
            }

            if ($snapshot->version->value !== $context->expectedVersion->value
                || $snapshot->version->value !== $context->observedVersion->value) {
                return AccountStatusPersistenceWriteResult::VersionConflict;
            }

            if ($snapshot->state !== $transition->from
                || $context->currentState !== $transition->from
                || $context->action !== $transition->action) {
                return AccountStatusPersistenceWriteResult::PersistenceRejected;
            }

            try {
                $this->insert($this->mapper->transition($transition, $context));
            } catch (PDOException $error) {
                if (in_array($error->getCode(), ['23505', '23514'], true)) {
                    return AccountStatusPersistenceWriteResult::PersistenceRejected;
                }
                throw $error;
            }

            return AccountStatusPersistenceWriteResult::Applied;
        });
    }

    /** @param callable(): AccountStatusPersistenceWriteResult $operation */
    private function withinTransaction(callable $operation): AccountStatusPersistenceWriteResult
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function lock(AccountId $accountId): void
    {
        $statement = $this->connection->prepare(
            'SELECT pg_advisory_xact_lock(hashtextextended(:account_id,0))',
        );
        $statement->execute(['account_id' => $accountId->value]);
    }

    /** @return array<string, mixed>|false */
    private function current(AccountId $accountId, bool $forUpdate): array|false
    {
        $sql = $this->selectSql()
            .' WHERE account_id=CAST(:account_id AS uuid) ORDER BY version DESC LIMIT 1';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->connection->prepare($sql);
        $statement->execute(['account_id' => $accountId->value]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function selectSql(): string
    {
        return 'SELECT account_id::text,version,entry_kind,previous_state,current_state,action,actor_id,to_char(occurred_at AT TIME ZONE \'UTC\',\'YYYY-MM-DD"T"HH24:MI:SS.US"Z"\') AS occurred_at,intent_id,context_version,legacy_account_version,entry_checksum FROM identity_access.account_status_lifecycle_transitions';
    }

    /** @param array<string, int|string|null> $parameters */
    private function insert(array $parameters): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.account_status_lifecycle_transitions(account_id,version,entry_kind,previous_state,current_state,action,actor_id,occurred_at,intent_id,context_version,legacy_account_version,entry_checksum) VALUES(CAST(:account_id AS uuid),:version,:entry_kind,:previous_state,:current_state,:action,:actor_id,CAST(:occurred_at AS timestamptz),:intent_id,:context_version,:legacy_account_version,:checksum)',
        );
        $statement->execute($parameters);
    }
}
