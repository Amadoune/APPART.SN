<?php

namespace Tests\PostgreSQL\HistoricalRedirect;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlHistoricalRedirectResolverIntegrationTest extends TestCase
{
    private PDO $connection;

    private HistoricalRedirectDecisionMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new HistoricalRedirectDecisionMapper;
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function corruptions(): iterable
    {
        yield 'checksum mismatch' => [['decision_checksum' => str_repeat('0', 64)]];
        yield 'invalid revision' => [['revision' => 0, 'integrity_status' => 'corrupted']];
        yield 'invalid qualification' => [['destination_qualification' => 'unknown', 'integrity_status' => 'corrupted']];
        yield 'declared corruption' => [['integrity_status' => 'corrupted']];
    }

    /** @param array<string, mixed> $changes */
    #[DataProvider('corruptions')]
    public function test_persisted_corruption_is_classified(array $changes): void
    {
        $source = $this->source();
        $row = $this->row($source) + ['integrity_status' => 'intact'];
        $row = array_replace($row, $changes);
        $row['decision_checksum'] ??= $this->mapper->checksum($row);
        $this->insert($row);

        self::assertSame(HistoricalRedirectStatus::Corrupted, $this->resolver()->resolve($source)->status);
    }

    public function test_invalid_source_read_from_postgresql_is_rejected_by_mapper(): void
    {
        $source = $this->source();
        $row = $this->row($source);
        $row['historical_canonical'] = 'not-a-public-canonical';
        $row['integrity_status'] = 'corrupted';
        $row['decision_checksum'] = $this->mapper->checksum($row);
        $this->insert($row);
        $stored = $this->connection->query('SELECT decision_id::text,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status FROM content_seo.historical_redirect_decisions')->fetchAll(PDO::FETCH_ASSOC);

        self::assertSame(HistoricalRedirectStatus::Corrupted, $this->mapper->toResolution($source, $stored)->status);
    }

    public function test_repeated_reads_are_deterministic(): void
    {
        $source = $this->source();
        $row = $this->row($source);
        $row['integrity_status'] = 'intact';
        $row['decision_checksum'] = $this->mapper->checksum($row);
        $this->insert($row);

        self::assertEquals($this->resolver()->resolve($source), $this->resolver()->resolve($source));
    }

    public function test_lookup_uses_the_dedicated_index_and_is_bounded(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT decision_id::text FROM content_seo.historical_redirect_decisions WHERE historical_canonical=:source ORDER BY decision_id LIMIT 2');
        $statement->execute(['source' => $this->source()->canonical->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));

        self::assertStringContainsString('historical_redirect_decisions_source_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_down_migration_and_forward_migration_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = file_get_contents($root.'013_historical_redirect_decisions.down.sql');
        $up = file_get_contents($root.'013_historical_redirect_decisions.sql');
        self::assertIsString($down);
        self::assertIsString($up);

        $this->connection->exec($down);
        self::assertSame(null, $this->connection->query("SELECT to_regclass('content_seo.historical_redirect_decisions')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('content_seo.historical_redirect_decisions', $this->connection->query("SELECT to_regclass('content_seo.historical_redirect_decisions')")->fetchColumn());
    }

    /** @param array<string, mixed> $row */
    private function insert(array $row): void
    {
        $statement = $this->connection->prepare('INSERT INTO content_seo.historical_redirect_decisions(decision_id,historical_canonical,destination_canonical,destination_qualification,revision,decision_checksum,integrity_status) VALUES(CAST(:decision_id AS uuid),:historical_canonical,:destination_canonical,:destination_qualification,:revision,:decision_checksum,:integrity_status)');
        $statement->execute($row);
    }

    /** @return array<string, mixed> */
    private function row(HistoricalCanonical $source): array
    {
        return ['decision_id' => '13100000-0000-4000-8000-000000000001', 'historical_canonical' => $source->canonical->value, 'destination_canonical' => 'https://appart.sn/annonces/nouvelle-annonce', 'destination_qualification' => 'current', 'revision' => 1];
    }

    private function resolver(): PostgreSqlHistoricalRedirectResolver
    {
        return new PostgreSqlHistoricalRedirectResolver($this->connection, $this->mapper);
    }

    private function source(): HistoricalCanonical
    {
        return HistoricalCanonical::declared(CanonicalUrl::fromString('https://appart.sn/annonces/ancienne-annonce'));
    }
}
