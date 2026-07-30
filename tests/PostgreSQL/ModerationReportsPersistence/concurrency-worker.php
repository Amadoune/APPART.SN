<?php

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    exit(2);
}
[$script, $barrier, $worker] = $arguments;
$time = new DateTimeImmutable('2026-07-29T12:01:00+00:00');
$id = static fn (int $suffix): string => sprintf('53000000-0000-4000-8000-%012d', $suffix);
$store = new PostgreSqlModerationCaseStore(
    PostgreSqlTestEnvironment::connection(),
    new ModerationPersistenceMapper,
);
$state = new ModerationCasePersistenceState(
    $id(9),
    'Listing',
    $id(509),
    'UnderReview',
    null,
    2,
    $id(900 + (int) $worker),
    hash('sha256', 'worker-'.$worker),
    $time,
    [new ModerationPersistenceRecord($id(109), ['category' => 'worker-'.$worker], $time)],
    [],
    [],
);

touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo $store->save($state, 1)->value;
