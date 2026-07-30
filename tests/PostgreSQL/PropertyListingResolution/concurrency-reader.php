<?php

use App\Infrastructure\PropertyListingResolution\PostgreSql\PostgreSqlPropertyListingsResolver;
use App\Infrastructure\PropertyListingResolution\PropertyListingsCheckpoint;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
$propertyId = $argv[3];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$page = (new PostgreSqlPropertyListingsResolver(
    PostgreSqlTestEnvironment::connection(),
    new PropertyListingsCheckpoint,
))->readPage($propertyId, null, 10);

echo json_encode($page->listingIds, JSON_THROW_ON_ERROR);
