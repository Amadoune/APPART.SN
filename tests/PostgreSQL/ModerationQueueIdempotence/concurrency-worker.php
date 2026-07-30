<?php

use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 4) {
    exit(2);
}
[$script, $barrier, $worker, $queueItemId] = $arguments;
$number = (int) $worker;
$id = static fn (int $suffix): string => sprintf('53a10000-0000-4000-8000-%012d', $suffix);
$store = new PostgreSqlModerationQueueStore(
    PostgreSqlTestEnvironment::connection(),
    new ModerationPersistenceMapper,
);

touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo $store->claim(
    $queueItemId,
    $id(40 + $number),
    $id(50 + $number),
    new DateTimeImmutable('2026-07-30T12:05:00+00:00'),
    new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
    $id(60 + $number),
    hash('sha256', 'worker-'.$worker),
)->value;
