<?php

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $number] = $argv;
$connection = PostgreSqlTestEnvironment::connection();
$mapper = new PostgreSqlPublicListingProjectionMapper;
$writer = new PostgreSqlPublicListingProjectionWriter($connection, $mapper, new PostgreSqlPublicListingProjectionReader($connection, $mapper));
$model = PublicListingReadModelFixture::make();
$record = PublicListingProjectionRecord::current($model->listingId, 'annonces/appartement-moderne-dakar', $model, new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1), PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000001'));
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
echo $writer->applyCurrent($record)->value;
