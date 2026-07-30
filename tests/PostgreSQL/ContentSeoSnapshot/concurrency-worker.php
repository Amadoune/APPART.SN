<?php

use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ContentSeoSourceSnapshotFixture;

require dirname(__DIR__, 3).'/vendor/autoload.php';
$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
echo (new PostgreSqlContentSeoSourceSnapshotWriter(PostgreSqlTestEnvironment::connection(), new ContentSeoSourceSnapshotMapper))->store(ContentSeoSourceSnapshotFixture::make())->value;
