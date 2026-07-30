<?php

namespace Tests\PostgreSQL\AccountStatusPersistence;

use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventOrchestrator;
use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventRequest;
use App\Application\AccountStatusEventRouting\DeterministicAccountStatusEventRouter;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\DeterministicAccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAccountStatusAtomicEventIntegrationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAccountRepository $accounts;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->accounts = new PostgreSqlAccountRepository(
            $this->connection,
            new AccountPersistenceMapper,
        );
        $this->accounts->add($this->account());
    }

    public function test_transition_and_routed_outbox_record_commit_together(): void
    {
        $result = $this->integration(
            new PostgreSqlPublicProjectionOutboxWriter(
                $this->connection,
                new PostgreSqlPublicProjectionOutboxMapper,
            ),
        )->transition($this->request());

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $result->status);
        self::assertSame(2, $this->transitionCount());
        self::assertSame(1, $this->outboxCount());
        $row = $this->connection->query(
            'SELECT source_module,aggregate_type,event_type,routing_destination,routing_version,routing_checksum
             FROM identity_access.public_projection_outbox_messages',
        )->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame('IdentityAccess', $row['source_module']);
        self::assertSame('AccountStatus', $row['aggregate_type']);
        self::assertSame('account.status.suspended', $row['event_type']);
        self::assertSame(
            'identity_access.account_status.lifecycle_facts',
            $row['routing_destination'],
        );
        self::assertSame(1, $row['routing_version']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $row['routing_checksum']);
    }

    public function test_outbox_rejection_rolls_back_lifecycle_and_outbox_together(): void
    {
        $result = $this->integration(
            new RejectingAccountStatusRoutedWriter,
        )->transition($this->request());

        self::assertSame(
            AccountStatusOrchestrationStatus::PersistenceCorrupted,
            $result->status,
        );
        self::assertSame(0, $this->transitionCount());
        self::assertSame(0, $this->outboxCount());
        self::assertSame(0, $this->accounts->find($this->id())?->version());
    }

    private function integration(
        PublicProjectionOutboxRoutedWriterV1 $writer,
    ): AccountStatusAtomicEventOrchestrator {
        $store = new PostgreSqlAccountStatusWorkflowStore(
            $this->connection,
            $this->accounts,
            new AccountStatusWorkflowMapper,
        );
        $orchestrator = new DeterministicAccountStatusOrchestrator(
            $store,
            new AccountStatusWorkflow,
            new PostgreSqlAccountStatusOrchestrationTransaction($this->connection),
        );

        return new AccountStatusAtomicEventOrchestrator(
            $orchestrator,
            new PostgreSqlAggregateOutboxTransaction($this->connection),
            new AccountStatusEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(
                new PublicProjectionDeliveryEventCatalog,
            ),
            new DeterministicAccountStatusEventRouter(
                new AccountStatusTransportSerializer,
            ),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('account-status-atomic'),
        );
    }

    private function request(): AccountStatusAtomicEventRequest
    {
        return new AccountStatusAtomicEventRequest(
            new AccountStatusContextV1(
                $this->id(),
                AccountStatusState::Active,
                new AccountStatusVersion(0),
                new AccountStatusVersion(0),
                AccountStatusAction::Suspend,
                new AccountStatusActorId('operator-49k'),
                new AccountStatusOccurredAt(
                    new DateTimeImmutable('2026-07-26T17:00:00+00:00'),
                ),
                new AccountStatusIntentId('intent-49k'),
            ),
            new DateTimeImmutable('2026-07-26T17:00:01+00:00'),
        );
    }

    private function account(): Account
    {
        $at = new DateTimeImmutable('2026-07-26T16:00:00+00:00');
        $account = Account::register(
            $this->id(),
            EmailAddress::fromString('atomic-account-status@example.test'),
            PhoneNumber::fromString('+221770049003'),
            PersonName::fromString('Atomic Account Status'),
            PasswordHash::fromString('$generic$v=1$salt-test$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $at->modify('+1 hour'),
            $at,
        );
        $account->releaseEvents();

        return $account;
    }

    private function id(): AccountId
    {
        return AccountId::fromString('49e00000-0000-4000-8000-000000000003');
    }

    private function transitionCount(): int
    {
        return (int) $this->connection->query(
            'SELECT COUNT(*) FROM identity_access.account_status_lifecycle_transitions',
        )->fetchColumn();
    }

    private function outboxCount(): int
    {
        return (int) $this->connection->query(
            'SELECT COUNT(*) FROM identity_access.public_projection_outbox_messages',
        )->fetchColumn();
    }
}

final readonly class RejectingAccountStatusRoutedWriter implements PublicProjectionOutboxRoutedWriterV1
{
    public function appendRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }
}
