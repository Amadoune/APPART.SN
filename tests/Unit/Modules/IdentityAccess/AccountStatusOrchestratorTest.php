<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\DeterministicAccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceWriteResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusStoredState;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountStatusOrchestratorTest extends TestCase
{
    public function test_found_state_is_decided_and_appended_once(): void
    {
        $store = ControlledAccountStatusStore::found($this->id(), AccountStatusState::Active, 0);
        $orchestrator = $this->orchestrator($store);

        $result = $orchestrator->transition($this->context());

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $result->status);
        self::assertSame(AccountStatusState::Suspended, $result->state);
        self::assertSame(0, $store->bootstrapCalls);
        self::assertSame(1, $store->appendCalls);
    }

    public function test_legacy_state_is_bootstrapped_then_read_and_decided_in_one_transaction(): void
    {
        $store = ControlledAccountStatusStore::legacy($this->id(), AccountStatusState::Active);
        $transaction = new ImmediateAccountStatusTransaction;
        $orchestrator = new DeterministicAccountStatusOrchestrator($store, new AccountStatusWorkflow, $transaction);

        $result = $orchestrator->transition($this->context());

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $result->status);
        self::assertSame(1, $store->bootstrapCalls);
        self::assertSame(1, $store->appendCalls);
        self::assertSame(1, $transaction->calls);
    }

    /** @return iterable<string, array{ControlledAccountStatusStore, AccountStatusContextV1, AccountStatusOrchestrationStatus}> */
    public static function closedResults(): iterable
    {
        $id = self::staticId();

        yield 'account missing' => [
            ControlledAccountStatusStore::missing($id),
            self::staticContext(),
            AccountStatusOrchestrationStatus::AccountMissing,
        ];
        yield 'persistence corrupted read' => [
            ControlledAccountStatusStore::corrupted($id),
            self::staticContext(),
            AccountStatusOrchestrationStatus::PersistenceCorrupted,
        ];
        yield 'already in state' => [
            ControlledAccountStatusStore::found($id, AccountStatusState::Active, 0),
            self::staticContext(AccountStatusAction::Reactivate),
            AccountStatusOrchestrationStatus::AlreadyInState,
        ];
        yield 'invalid context' => [
            ControlledAccountStatusStore::found($id, AccountStatusState::Active, 0),
            self::staticContext(observedVersion: 1),
            AccountStatusOrchestrationStatus::InvalidContext,
        ];
        yield 'version conflict' => [
            ControlledAccountStatusStore::found($id, AccountStatusState::Active, 0, AccountStatusPersistenceWriteResult::VersionConflict),
            self::staticContext(),
            AccountStatusOrchestrationStatus::VersionConflict,
        ];
        yield 'persistence rejected write' => [
            ControlledAccountStatusStore::found($id, AccountStatusState::Active, 0, AccountStatusPersistenceWriteResult::PersistenceRejected),
            self::staticContext(),
            AccountStatusOrchestrationStatus::PersistenceRejected,
        ];
    }

    #[DataProvider('closedResults')]
    public function test_results_are_owned_and_closed(
        ControlledAccountStatusStore $store,
        AccountStatusContextV1 $context,
        AccountStatusOrchestrationStatus $expected,
    ): void {
        self::assertSame($expected, $this->orchestrator($store)->transition($context)->status);
    }

    public function test_unexpected_durable_failure_is_not_reported_as_missing(): void
    {
        $transaction = new FailingAccountStatusTransaction;
        $result = (new DeterministicAccountStatusOrchestrator(
            ControlledAccountStatusStore::found($this->id(), AccountStatusState::Active, 0),
            new AccountStatusWorkflow,
            $transaction,
        ))->transition($this->context());

        self::assertSame(AccountStatusOrchestrationStatus::PersistenceCorrupted, $result->status);
    }

    private function orchestrator(ControlledAccountStatusStore $store): DeterministicAccountStatusOrchestrator
    {
        return new DeterministicAccountStatusOrchestrator(
            $store,
            new AccountStatusWorkflow,
            new ImmediateAccountStatusTransaction,
        );
    }

    private function context(): AccountStatusContextV1
    {
        return self::staticContext();
    }

    private function id(): AccountId
    {
        return self::staticId();
    }

    private static function staticId(): AccountId
    {
        return AccountId::fromString('49e00000-0000-4000-8000-000000000001');
    }

    private static function staticContext(
        AccountStatusAction $action = AccountStatusAction::Suspend,
        int $observedVersion = 0,
    ): AccountStatusContextV1 {
        return new AccountStatusContextV1(
            self::staticId(),
            AccountStatusState::Active,
            new AccountStatusVersion(0),
            new AccountStatusVersion($observedVersion),
            $action,
            new AccountStatusActorId('operator-49e'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T10:00:00+00:00')),
            new AccountStatusIntentId('intent-49e'),
        );
    }
}

final class ControlledAccountStatusStore implements AccountStatusWorkflowStore
{
    public int $bootstrapCalls = 0;

    public int $appendCalls = 0;

    /** @param list<AccountStatusPersistenceReadResult> $reads */
    private function __construct(
        private array $reads,
        private readonly AccountStatusPersistenceWriteResult $writeResult,
    ) {}

    public static function found(
        AccountId $id,
        AccountStatusState $state,
        int $version,
        AccountStatusPersistenceWriteResult $writeResult = AccountStatusPersistenceWriteResult::Applied,
    ): self {
        return new self([
            AccountStatusPersistenceReadResult::found(
                new AccountStatusStoredState($id, $state, new AccountStatusVersion($version)),
            ),
        ], $writeResult);
    }

    public static function legacy(AccountId $id, AccountStatusState $state): self
    {
        return new self([
            AccountStatusPersistenceReadResult::legacyUninitialized($id, $state),
            AccountStatusPersistenceReadResult::found(
                new AccountStatusStoredState($id, $state, new AccountStatusVersion(0)),
            ),
        ], AccountStatusPersistenceWriteResult::Applied);
    }

    public static function missing(AccountId $id): self
    {
        return new self(
            [AccountStatusPersistenceReadResult::accountMissing($id)],
            AccountStatusPersistenceWriteResult::AccountMissing,
        );
    }

    public static function corrupted(AccountId $id): self
    {
        return new self(
            [AccountStatusPersistenceReadResult::persistenceRejected($id)],
            AccountStatusPersistenceWriteResult::PersistenceRejected,
        );
    }

    public function read(AccountId $accountId): AccountStatusPersistenceReadResult
    {
        return array_shift($this->reads) ?? AccountStatusPersistenceReadResult::persistenceRejected($accountId);
    }

    public function bootstrap(AccountId $accountId): AccountStatusPersistenceWriteResult
    {
        $this->bootstrapCalls++;

        return AccountStatusPersistenceWriteResult::Applied;
    }

    public function append(
        AccountStatusTransition $transition,
        AccountStatusContextV1 $context,
    ): AccountStatusPersistenceWriteResult {
        $this->appendCalls++;

        return $this->writeResult;
    }
}

final class ImmediateAccountStatusTransaction implements AccountStatusOrchestrationTransaction
{
    public int $calls = 0;

    public function run(callable $operation): AccountStatusOrchestrationResult
    {
        $this->calls++;

        return $operation();
    }
}

final class FailingAccountStatusTransaction implements AccountStatusOrchestrationTransaction
{
    public function run(callable $operation): AccountStatusOrchestrationResult
    {
        throw new \RuntimeException('durable failure');
    }
}
