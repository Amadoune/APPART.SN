<?php

namespace Tests\PostgreSQL\LegacyMigrationOwnerSource;

use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationCutoverRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationInventoryWriteResult;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationQuarantineRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationReconciliationRevisionState;
use Appart\Modules\LegacyMigration\Application\OwnerSource\LegacyMigrationWaveRevisionState;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationObservedAt;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationSubjectKey;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\LegacyMigrationOwnerSourceMapper;
use Appart\Modules\LegacyMigration\Infrastructure\Persistence\PostgreSql\PostgreSqlLegacyMigrationOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLegacyMigrationOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlLegacyMigrationOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $sql = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.sql');
        $this->connection->exec($sql);
        $this->connection->exec('TRUNCATE legacy_migration.owner_current_index, legacy_migration.owner_revision_journal');
        $this->source = new PostgreSqlLegacyMigrationOwnerSource($this->connection, new LegacyMigrationOwnerSourceMapper);
    }

    public function test_five_streams_are_independent_and_temporal(): void
    {
        $e = self::at('10:00:00');
        $r = self::at('10:00:01');
        $subject = new LegacyMigrationSubjectKey('scope:1');
        self::assertSame(LegacyMigrationInventoryWriteResult::Applied, $this->source->appendInventory(new LegacyMigrationInventoryRevisionState($subject, 1, LegacyMigrationInventoryStatusV1::Available, $e, $r)));
        self::assertSame(LegacyMigrationInventoryWriteResult::AlreadyApplied, $this->source->appendInventory(new LegacyMigrationInventoryRevisionState($subject, 1, LegacyMigrationInventoryStatusV1::Available, $e, $r)));
        $this->source->appendWave(new LegacyMigrationWaveRevisionState($subject, 1, LegacyMigrationWaveStatusV1::Ready, $e, $r));
        $this->source->appendReconciliation(new LegacyMigrationReconciliationRevisionState($subject, 1, LegacyMigrationReconciliationStatusV1::Matched, $e, $r));
        $this->source->appendQuarantine(new LegacyMigrationQuarantineRevisionState($subject, 1, LegacyMigrationQuarantineStatusV1::Empty, $e, $r));
        $this->source->appendCutover(new LegacyMigrationCutoverRevisionState($subject, 1, LegacyMigrationCutoverStatusV1::Ready, $e, $r));
        $observed = new LegacyMigrationObservedAt(self::at('11:00:00'));
        self::assertSame(LegacyMigrationInventoryStatusV1::Available, $this->source->readInventory($subject, $observed)->status);
        self::assertSame(LegacyMigrationWaveStatusV1::Ready, $this->source->readWave($subject, $observed)->status);
        self::assertSame(LegacyMigrationReconciliationStatusV1::Matched, $this->source->readReconciliation($subject, $observed)->status);
        self::assertSame(LegacyMigrationQuarantineStatusV1::Empty, $this->source->readQuarantine($subject, $observed)->status);
        self::assertSame(LegacyMigrationCutoverStatusV1::Ready, $this->source->readCutover($subject, $observed)->status);
    }

    public function test_external_rollback_is_preserved(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(LegacyMigrationInventoryWriteResult::Applied, $this->source->appendInventory(new LegacyMigrationInventoryRevisionState('scope:rollback', 1, LegacyMigrationInventoryStatusV1::Available, self::at('10:00:00'), self::at('10:00:01'))));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(LegacyMigrationInventoryStatusV1::Missing, $this->source->readInventory(new LegacyMigrationSubjectKey('scope:rollback'), new LegacyMigrationObservedAt(self::at('11:00:00')))->status);
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-03T'.$time.'.123456Z');
    }
}
