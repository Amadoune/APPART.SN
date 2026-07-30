<?php

namespace Tests\PostgreSQL\AdministrationAuditPublicAppend;

use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditAppendResultV1;
use Appart\Modules\AdministrationAudit\Application\PublicAuditAppend\AdministrationAuditOutcomeV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\AdministrationAudit\AdministrationAuditPublicAppendImplementationTest;

final class PostgreSqlAdministrationAuditPublicAppendTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAdministrationAuditAppendV1 $append;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->append = new PostgreSqlAdministrationAuditAppendV1(
            new AdministrationAuditAppendConnection($this->connection),
            new AdministrationAuditAppendMapperV1,
        );
    }

    #[Test]
    public function migration_append_idempotence_and_divergence_are_owner_local(): void
    {
        $record = AdministrationAuditPublicAppendImplementationTest::record();

        self::assertSame(AdministrationAuditAppendResultV1::Applied, $this->append->append($record));
        self::assertSame(AdministrationAuditAppendResultV1::AlreadyApplied, $this->append->append($record));
        self::assertSame(
            AdministrationAuditAppendResultV1::DivergentRecord,
            $this->append->append(
                AdministrationAuditPublicAppendImplementationTest::record(AdministrationAuditOutcomeV1::Rejected),
            ),
        );
        self::assertSame(
            1,
            (int) $this->connection->query(
                'SELECT count(*) FROM administration_audit.public_append_records',
            )->fetchColumn(),
        );
    }

    #[Test]
    public function an_enclosing_transaction_uses_a_savepoint_and_remains_owner_controlled(): void
    {
        $this->connection->beginTransaction();

        self::assertSame(
            AdministrationAuditAppendResultV1::Applied,
            $this->append->append(AdministrationAuditPublicAppendImplementationTest::record()),
        );
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame(
            0,
            (int) $this->connection->query(
                'SELECT count(*) FROM administration_audit.public_append_records',
            )->fetchColumn(),
        );
    }

    #[Test]
    public function migration_rollback_is_complete_and_preserves_historical_administration_audit(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = (string) file_get_contents($root.'071_administration_audit_public_append.down.sql');
        $up = (string) file_get_contents($root.'071_administration_audit_public_append.sql');

        $this->connection->exec($down);
        self::assertSame(
            0,
            (int) $this->connection->query(
                "SELECT count(*) FROM information_schema.tables
                 WHERE table_schema='administration_audit' AND table_name='public_append_records'",
            )->fetchColumn(),
        );
        self::assertSame(
            1,
            (int) $this->connection->query(
                "SELECT count(*) FROM information_schema.tables
                 WHERE table_schema='administration_audit' AND table_name='administrative_actions'",
            )->fetchColumn(),
        );
        $this->connection->exec($up);
    }

    #[Test]
    public function concurrent_identical_appends_converge_to_applied_and_already_applied(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'audit-append-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($worker = 1; $worker <= 2; $worker++) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start public append concurrency worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        self::assertFileExists($barrier.'.ready.1');
        self::assertFileExists($barrier.'.ready.2');
        touch($barrier.'.start');

        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            self::assertSame(0, proc_close($process), $error);
        }
        sort($results);
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(
            1,
            (int) $this->connection->query(
                'SELECT count(*) FROM administration_audit.public_append_records',
            )->fetchColumn(),
        );

        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }
}
