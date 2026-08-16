<?php

namespace Tests\PostgreSQL\PublicGeographySource;

use App\Application\PublicGeographyRefresh\AffectedPublicGeographyTerminalStatus;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItem;
use App\Application\PublicGeographySource\PublicGeographyBreadcrumbItemV2;
use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyDecisionStatusV2;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyReadStatus;
use App\Application\PublicGeographySource\PublicGeographyRevisionVectorItemV2;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlAffectedPublicGeographyTerminalReader;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyMapper;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyReader;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyWriter;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPublicGeographySourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPublicGeographyMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new PostgreSqlPublicGeographyMapper;
    }

    public function test_missing_write_read_idempotence_and_revision_contract(): void
    {
        $d = $this->decision();
        self::assertSame(PublicGeographyReadStatus::Missing, $this->reader()->read($d->placeId)->status);
        self::assertSame(PublicGeographyWriteResult::Applied, $this->writer()->store($d));
        self::assertSame(PublicGeographyWriteResult::AlreadyApplied, $this->writer()->store($d));
        $r = $this->reader()->read($d->placeId);
        self::assertSame(PublicGeographyReadStatus::Found, $r->status);
        self::assertEquals($d, $r->decision);
        self::assertEquals($d->revision, $this->reader()->stableRevisionForPlace($d->placeId));
    }

    public function test_corruption_is_explicit(): void
    {
        $d = $this->decision();
        $this->writer()->store($d);
        $this->connection->exec("UPDATE public_geography.decisions SET payload_checksum='".str_repeat('0', 64)."',revision_checksum='".str_repeat('0', 64)."'");
        self::assertSame(PublicGeographyReadStatus::Corrupted, $this->reader()->read($d->placeId)->status);
    }

    public function test_obsolete_and_same_version_divergence_are_explicit(): void
    {
        self::assertSame(PublicGeographyWriteResult::Applied, $this->writer()->store($this->decision(2, 'Dakar')));
        self::assertSame(PublicGeographyWriteResult::RejectedObsolete, $this->writer()->store($this->decision(1, 'Dakar')));
        self::assertSame(PublicGeographyWriteResult::Divergent, $this->writer()->store($this->decision(2, 'Plateau')));
    }

    public function test_external_rollback_is_complete(): void
    {
        $d = $this->decision();
        $this->connection->beginTransaction();
        $this->writer()->store($d);
        $this->connection->rollBack();
        self::assertSame(PublicGeographyReadStatus::Missing, $this->reader()->read($d->placeId)->status);
    }

    public function test_v2_write_read_replay_and_affected_terminal_lookup(): void
    {
        $decision = $this->decisionV2();
        self::assertSame(PublicGeographyWriteResult::Applied, $this->writer()->store($decision));
        self::assertSame(PublicGeographyWriteResult::AlreadyApplied, $this->writer()->store($decision));

        $read = $this->reader()->read($decision->terminalPlaceId);
        self::assertSame(PublicGeographyReadStatus::Found, $read->status);
        self::assertEquals($decision, $read->decision);
        self::assertSame(3, $read->decision?->revision->watermarkVersion());

        $affected = (new PostgreSqlAffectedPublicGeographyTerminalReader($this->connection))->read('c3120000-0000-4000-8000-000000000001', null, 10);
        self::assertSame(AffectedPublicGeographyTerminalStatus::Available, $affected->status);
        self::assertSame([$decision->terminalPlaceId], $affected->terminalPlaceIds);
    }

    public function test_concurrent_identical_writes_converge_without_double_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-public-geography-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Public Geography worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException('Public Geography worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_geography.decisions')->fetchColumn());
    }

    private function decision(int $version = 1, string $locality = 'Dakar'): PublicGeographyDecision
    {
        $items = [new PublicGeographyBreadcrumbItem('Accueil', 'https://appart.sn/'), new PublicGeographyBreadcrumbItem($locality, 'https://appart.sn/dakar')];
        $payload = json_encode(['locality' => $locality, 'breadcrumb' => array_map(static fn ($i) => ['label' => $i->label, 'url' => $i->url], $items)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new PublicGeographyDecision('place:dakar', (new PublicGeographyRevisionStrategy)->revise($version, $payload, 'place:'.$version.':published'), $locality, $items);
    }

    private function decisionV2(): PublicGeographyDecisionV2
    {
        $breadcrumb = [
            new PublicGeographyBreadcrumbItemV2('c3120000-0000-4000-8000-000000000001', 'country', 'Senegal', null, 1),
            new PublicGeographyBreadcrumbItemV2('c3120000-0000-4000-8000-000000000002', 'region', 'Dakar Region', 'c3120000-0000-4000-8000-000000000001', 1),
            new PublicGeographyBreadcrumbItemV2('c3120000-0000-4000-8000-000000000003', 'city', 'Dakar', 'c3120000-0000-4000-8000-000000000002', 1),
        ];
        $vector = array_map(static fn (PublicGeographyBreadcrumbItemV2 $item): PublicGeographyRevisionVectorItemV2 => new PublicGeographyRevisionVectorItemV2($item->placeId, $item->aggregateVersion), $breadcrumb);
        $payload = '{"schemaVersion":"public-geography-place-representation-v2","terminalPlaceId":"c3120000-0000-4000-8000-000000000003","status":"available","locality":"Dakar","breadcrumb":[{"placeId":"c3120000-0000-4000-8000-000000000001","type":"country","officialName":"Senegal","parentPlaceId":null,"aggregateVersion":1},{"placeId":"c3120000-0000-4000-8000-000000000002","type":"region","officialName":"Dakar Region","parentPlaceId":"c3120000-0000-4000-8000-000000000001","aggregateVersion":1},{"placeId":"c3120000-0000-4000-8000-000000000003","type":"city","officialName":"Dakar","parentPlaceId":"c3120000-0000-4000-8000-000000000002","aggregateVersion":1}],"revisionVector":[{"placeId":"c3120000-0000-4000-8000-000000000001","aggregateVersion":1},{"placeId":"c3120000-0000-4000-8000-000000000002","aggregateVersion":1},{"placeId":"c3120000-0000-4000-8000-000000000003","aggregateVersion":1}]}';

        return new PublicGeographyDecisionV2('c3120000-0000-4000-8000-000000000003', PublicGeographyDecisionStatusV2::Available, 'Dakar', $breadcrumb, $vector, (new PublicGeographyRevisionStrategy)->revise(3, $payload, 'listing:rc2:public-geography-v2'));
    }

    private function reader(): PostgreSqlPublicGeographyReader
    {
        return new PostgreSqlPublicGeographyReader($this->connection, $this->mapper);
    }

    private function writer(): PostgreSqlPublicGeographyWriter
    {
        return new PostgreSqlPublicGeographyWriter($this->connection, $this->mapper);
    }
}
