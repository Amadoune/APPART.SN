<?php

use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifestEntry;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationManager;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationValidator;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
$generation = PublicProjectionGenerationId::fromString($argv[3]);
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$connection = PostgreSqlTestEnvironment::connection();
$validator = new PostgreSqlPublicProjectionGenerationValidator($connection, new PostgreSqlPublicListingProjectionMapper);
$manager = new PostgreSqlPublicProjectionGenerationManager($connection, $validator);
$manifest = new PublicProjectionGenerationManifest([new PublicProjectionGenerationManifestEntry('98000000-0000-4000-8000-000000000011', new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1))]);
echo $manager->activate($generation, $manifest)->value;
