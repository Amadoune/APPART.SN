<?php

namespace Tests\PostgreSQL\Media;

use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Domain\Exception\MediaCollectionIdConflict;
use Appart\Modules\Media\Domain\Exception\MediaIdConflict;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\Media\FakeMediaCollectionRegistryHarness;

final class PostgreSqlMediaCollectionConcurrencyTest extends TestCase
{
    #[DataProvider('modes')]
    public function test_two_processes_produce_exactly_one_winner(string $mode, string $loser): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $repository = new PostgreSqlMediaCollectionRepository($connection, new MediaCollectionMapper);
        $h = new FakeMediaCollectionRegistryHarness;
        if ($mode === 'save') {
            $root = $h->emptyCollection();
            $repository->add($root);
            $h->addMedia($root, $h->firstMediaId(), 1);
            $repository->saveWithMediaReservation($root, $h->firstMediaId(), 0);
        }
        $results = $this->workers($mode);
        self::assertCount(1, array_filter($results, fn ($r) => $r === 'ok'));
        self::assertCount(1, array_filter($results, fn ($r) => $r === $loser));
        self::assertSame($mode === 'media' ? 2 : 1, (int) $connection->query('SELECT count(*) FROM media.media_collections')->fetchColumn());
        self::assertSame($mode === 'collection' ? 0 : 1, (int) $connection->query('SELECT count(*) FROM media.media_id_reservations')->fetchColumn());
        self::assertSame($mode === 'collection' ? 0 : 1, (int) $connection->query('SELECT count(*) FROM media.media_items')->fetchColumn());
    }

    public static function modes(): array
    {
        return [['collection', MediaCollectionIdConflict::class], ['media', MediaIdConflict::class], ['save', ConcurrentMediaCollectionModification::class]];
    }

    /** @return list<string> */
    private function workers(string $mode): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-media-pg-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($i = 1; $i <= 2; $i++) {
            $pipes = [];
            $p = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $mode, $barrier, (string) $i], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($p)) {
                throw new RuntimeException('Unable to start Media worker.');
            }$processes[] = [$p, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Media workers did not reach barrier.');
        }touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$p,$pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            $exit = proc_close($p);
            if ($exit !== 0 || $error !== '') {
                throw new RuntimeException('Media worker failed.');
            }
        }foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
