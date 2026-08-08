<?php

namespace Tests\PostgreSQL\ContentSeoOwnerSource;

use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentWriteResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoWriteResult;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoOwnerSourceMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlContentSeoOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlContentSeoOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/078_editorial_content_operational_seo_owner_source.sql';
        $this->connection->exec((string) file_get_contents($migration));
        $this->connection->exec('TRUNCATE content_seo.editorial_seo_current_index, content_seo.editorial_seo_revision_journal');
        $this->source = new PostgreSqlContentSeoOwnerSource($this->connection, new ContentSeoOwnerSourceMapper);
    }

    public function test_append_temporal_read_idempotence_and_current_index_are_owner_local(): void
    {
        $resource = new ContentSeoPublicResourceKey('editorial/guides/dakar');
        $first = new EditorialContentRevisionState($resource, 1, EditorialContentStatusV1::Unpublished, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        $second = new EditorialContentRevisionState($resource, 2, EditorialContentStatusV1::Published, new DateTimeImmutable('2026-08-02T11:00:00Z'), new DateTimeImmutable('2026-08-02T11:00:01Z'));

        self::assertSame(EditorialContentWriteResult::Applied, $this->source->appendEditorial($first));
        self::assertSame(EditorialContentWriteResult::AlreadyApplied, $this->source->appendEditorial($first));
        self::assertSame(EditorialContentWriteResult::Applied, $this->source->appendEditorial($second));
        self::assertSame(EditorialContentStatusV1::Unpublished, $this->source->readEditorial($resource, new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T10:30:00Z')))->status);
        self::assertSame(EditorialContentStatusV1::Published, $this->source->readEditorial($resource, new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T12:00:00Z')))->status);
        self::assertCount(2, $this->source->editorialHistory($resource));
        self::assertSame(2, (int) $this->connection->query("SELECT revision FROM content_seo.editorial_seo_current_index WHERE stream_type='editorial'")->fetchColumn());
    }

    public function test_streams_are_independent_and_version_conflicts_fail_closed(): void
    {
        $resource = new ContentSeoPublicResourceKey('editorial/guides/dakar');
        $seo = new OperationalSeoRevisionState($resource, 1, OperationalSeoStatusV1::Indexable, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        $skipped = new OperationalSeoRevisionState($resource, 3, OperationalSeoStatusV1::NoIndex, new DateTimeImmutable('2026-08-02T11:00:00Z'), new DateTimeImmutable('2026-08-02T11:00:01Z'));

        self::assertSame(OperationalSeoWriteResult::Applied, $this->source->appendOperationalSeo($seo));
        self::assertSame(OperationalSeoWriteResult::VersionConflict, $this->source->appendOperationalSeo($skipped));
        self::assertSame(OperationalSeoStatusV1::Indexable, $this->source->readOperationalSeo($resource, new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T12:00:00Z')))->status);
        self::assertSame(EditorialContentStatusV1::Missing, $this->source->readEditorial($resource, new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02T12:00:00Z')))->status);
    }

    public function test_external_transaction_owns_rollback_while_source_uses_a_savepoint(): void
    {
        $resource = new ContentSeoPublicResourceKey('editorial/guides/saint-louis');
        $state = new EditorialContentRevisionState($resource, 1, EditorialContentStatusV1::Published, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));

        $this->connection->beginTransaction();
        self::assertSame(EditorialContentWriteResult::Applied, $this->source->appendEditorial($state));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM content_seo.editorial_seo_revision_journal WHERE resource_key='editorial/guides/saint-louis'")->fetchColumn());
    }
}
