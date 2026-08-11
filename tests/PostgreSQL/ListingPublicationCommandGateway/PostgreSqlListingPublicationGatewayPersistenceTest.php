<?php

namespace Tests\PostgreSQL\ListingPublicationCommandGateway;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationGatewayPersistence;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingPublicationGatewayPersistenceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlListingPublicationGatewayPersistence $persistence;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->persistence = new PostgreSqlListingPublicationGatewayPersistence($this->connection);
    }

    public function test_ledger_reservation_completion_and_replay_are_durable(): void
    {
        $command = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $checksum = hash('sha256', 'begin-review');
        $result = $this->persistence->run(function () use ($command, $checksum): bool {
            self::assertTrue($this->persistence->reserve($command, '11111111-1111-4111-8111-111111111111', 'begin_review', $checksum, new DateTimeImmutable('2026-08-11T10:00:00+00:00')));

            return $this->persistence->complete($command, $checksum, ListingPublicationCommandStatus::Applied, 3, 2);
        });
        self::assertTrue($result);
        $record = $this->persistence->find($command);
        self::assertNotNull($record);
        self::assertSame(ListingPublicationCommandStatus::Applied, $record->status);
        self::assertSame(3, $record->workflowVersion);
        self::assertSame(2, $record->aggregateVersion);
    }

    public function test_same_command_cannot_be_reserved_twice(): void
    {
        $command = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
        $checksum = hash('sha256', 'approve');
        $this->persistence->run(function () use ($command, $checksum): void {
            self::assertTrue($this->persistence->reserve($command, '22222222-2222-4222-8222-222222222222', 'approve_and_publish', $checksum, new DateTimeImmutable('2026-08-11T10:00:00+00:00')));
            self::assertFalse($this->persistence->reserve($command, '22222222-2222-4222-8222-222222222222', 'approve_and_publish', $checksum, new DateTimeImmutable('2026-08-11T10:00:00+00:00')));
        });
    }

    public function test_external_rollback_is_preserved_and_rollback_migration_is_reversible(): void
    {
        $this->connection->beginTransaction();
        $this->persistence->run(function (): void {
            self::assertTrue($this->persistence->reserve('cccccccc-cccc-4ccc-8ccc-cccccccccccc', '33333333-3333-4333-8333-333333333333', 'begin_review', hash('sha256', 'rollback'), new DateTimeImmutable('2026-08-11T10:00:00+00:00')));
        });
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertNull($this->persistence->find('cccccccc-cccc-4ccc-8ccc-cccccccccccc'));

        $root = dirname(__DIR__, 3);
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/097_listing_publication_command_gateway.down.sql'));
        self::assertFalse((bool) $this->connection->query("SELECT to_regclass('listing_lifecycle.publication_command_gateway_ledger') IS NOT NULL")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/ListingLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/097_listing_publication_command_gateway.sql'));
        self::assertTrue((bool) $this->connection->query("SELECT to_regclass('listing_lifecycle.publication_command_gateway_ledger') IS NOT NULL")->fetchColumn());
    }
}
