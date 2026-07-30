<?php

namespace Tests\PostgreSQL\IdentityAccessCompletion;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureReadStatus;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountClosureStateReader;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAccountClosureStateReaderTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function absence_is_the_additive_legacy_open_state(): void
    {
        $result = $this->reader()->read($this->accountId());

        self::assertSame(AccountClosureReadStatus::LegacyOpen, $result->status);
        self::assertSame(AccountClosureState::Open, $result->state);
        self::assertSame(0, $result->version);
    }

    #[Test]
    public function it_reads_the_owner_state_without_touching_historical_account(): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access_completion.account_closures
             (account_id,state,version,closed_at,actor_id,reason_category,policy_version,retention_class,
              legal_hold,session_checkpoint,last_intent_id,last_intent_checksum,updated_at)
             VALUES(CAST(:account_id AS uuid),\'Closed\',3,now(),CAST(:account_id AS uuid),\'user_request\',
              \'v1\',\'standard\',false,2,\'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb\',:checksum,now())',
        );
        $statement->execute([
            'account_id' => $this->accountId()->value,
            'checksum' => str_repeat('a', 64),
        ]);

        $result = $this->reader()->read($this->accountId());

        self::assertSame(AccountClosureReadStatus::Found, $result->status);
        self::assertSame(AccountClosureState::Closed, $result->state);
        self::assertSame(3, $result->version);
        self::assertSame(0, (int) $this->connection->query(
            'SELECT count(*) FROM identity_access.accounts',
        )->fetchColumn());
    }

    private function reader(): PostgreSqlAccountClosureStateReader
    {
        return new PostgreSqlAccountClosureStateReader($this->connection);
    }

    private function accountId(): AccountId
    {
        return AccountId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    }
}
