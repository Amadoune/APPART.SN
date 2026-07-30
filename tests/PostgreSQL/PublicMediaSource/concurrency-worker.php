<?php

use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaMapper;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicMediaDecisionFixture;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo (new PostgreSqlPublicMediaWriter(
    PostgreSqlTestEnvironment::connection(),
    new PostgreSqlPublicMediaMapper,
))->store(PublicMediaDecisionFixture::make())->value;
