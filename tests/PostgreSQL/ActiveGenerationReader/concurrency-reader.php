<?php

use App\Infrastructure\ActiveGenerationReader\PostgreSql\PostgreSqlActiveGenerationMapper;
use App\Infrastructure\ActiveGenerationReader\PostgreSql\PostgreSqlActiveGenerationReader;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$result = (new PostgreSqlActiveGenerationReader(
    PostgreSqlTestEnvironment::connection(),
    new PostgreSqlActiveGenerationMapper,
))->read();

echo $result->generation?->id->value ?? $result->status->value;
