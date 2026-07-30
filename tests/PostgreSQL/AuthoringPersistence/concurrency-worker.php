<?php

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingDraftMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingDraftStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    exit(2);
}
[$script, $barrier, $worker] = $arguments;
$store = new PostgreSqlListingDraftStore(PostgreSqlTestEnvironment::connection(), new ListingDraftMapper);
$state = new ListingDraftState(
    '56000000-0000-4000-8000-000000000002',
    '56000000-0000-4000-8000-000000000001',
    'Concurrent '.$worker,
    'Description',
    'sale',
    100000,
    'XOF',
    0,
    '2026-08-01',
    'platform',
    2,
    sprintf('56000000-0000-4000-8002-%012d', (int) $worker),
    hash('sha256', 'concurrent-'.$worker),
);

touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo $store->save($state, 1)->value;
