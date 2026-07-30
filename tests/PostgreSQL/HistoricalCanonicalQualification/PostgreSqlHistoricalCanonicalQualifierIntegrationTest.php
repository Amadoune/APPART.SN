<?php

namespace Tests\PostgreSQL\HistoricalCanonicalQualification;

use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlHistoricalCanonicalQualifierIntegrationTest extends TestCase
{
    private PDO $connection;

    private HistoricalCanonicalQualificationMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new HistoricalCanonicalQualificationMapper;
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function corruptions(): iterable
    {
        yield 'checksum mismatch' => [['decision_checksum' => str_repeat('0', 64)]];
        yield 'invalid revision' => [['revision' => 0, 'integrity_status' => 'corrupted']];
        yield 'unknown qualification' => [['qualification' => 'unknown', 'integrity_status' => 'corrupted']];
        yield 'declared corruption' => [['integrity_status' => 'corrupted']];
    }

    /** @param array<string, mixed> $changes */
    #[DataProvider('corruptions')]
    public function test_corruption_is_explicit(array $changes): void
    {
        $canonical = $this->canonical();
        $row = array_replace($this->row($canonical) + ['integrity_status' => 'intact'], $changes);
        $row['decision_checksum'] ??= $this->mapper->checksum($row);
        $this->insert($row);

        self::assertSame(HistoricalCanonicalQualificationStatus::Corrupted, $this->qualifier()->qualify($canonical)->status);
    }

    public function test_repeated_reads_are_deterministic(): void
    {
        $canonical = $this->canonical();
        $row = $this->row($canonical) + ['integrity_status' => 'intact'];
        $row['decision_checksum'] = $this->mapper->checksum($row);
        $this->insert($row);

        self::assertEquals($this->qualifier()->qualify($canonical), $this->qualifier()->qualify($canonical));
    }

    public function test_lookup_uses_dedicated_index_and_limit(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT decision_id::text FROM content_seo.historical_canonical_qualifications WHERE canonical=:canonical ORDER BY decision_id LIMIT 2');
        $statement->execute(['canonical' => $this->canonical()->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));

        self::assertStringContainsString('historical_canonical_qualifications_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'014_historical_canonical_qualifications.down.sql');
        $up = file_get_contents($root.'014_historical_canonical_qualifications.sql');
        self::assertIsString($down);
        self::assertIsString($up);

        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('content_seo.historical_canonical_qualifications')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('content_seo.historical_canonical_qualifications', $this->connection->query("SELECT to_regclass('content_seo.historical_canonical_qualifications')")->fetchColumn());
    }

    /** @param array<string, mixed> $row */
    private function insert(array $row): void
    {
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_canonical_qualifications(decision_id,canonical,qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:canonical,:qualification,:revision,:decision_checksum,:integrity_status)');
        $statement->execute($row);
    }

    /** @return array<string, mixed> */
    private function row(CanonicalUrl $canonical): array
    {
        return ['decision_id' => '14100000-0000-4000-8000-000000000001', 'canonical' => $canonical->value, 'qualification' => 'historical', 'revision' => 1];
    }

    private function qualifier(): PostgreSqlHistoricalCanonicalQualifier
    {
        return new PostgreSqlHistoricalCanonicalQualifier($this->connection, $this->mapper);
    }

    private function canonical(): CanonicalUrl
    {
        return CanonicalUrl::fromString('https://appart.sn/annonces/canonical-publique');
    }
}
