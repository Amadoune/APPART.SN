<?php

use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\Media\FakeMediaCollectionRegistryHarness;

require dirname(__DIR__, 3).'/vendor/autoload.php';
[$script,$mode,$barrier,$worker] = $argv;
$h = new FakeMediaCollectionRegistryHarness;
$repository = new PostgreSqlMediaCollectionRepository(PostgreSqlTestEnvironment::connection(), new MediaCollectionMapper);
if ($mode === 'collection') {
    $collection = $h->emptyCollection();
} elseif ($mode === 'media') {
    $collection = $h->emptyCollection($worker === '1' ? $h->primaryId() : $h->distinctId());
    $repository->add($collection);
    $h->addMedia($collection, $h->firstMediaId(), 1);
} else {
    $collection = $repository->find($h->primaryId());
    if ($collection === null) {
        exit(2);
    }$h->mutate($collection);
}
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
try {
    if ($mode === 'collection') {
        $repository->add($collection);
    } elseif ($mode === 'media') {
        $repository->saveWithMediaReservation($collection, $h->firstMediaId(), 0);
    } else {
        $repository->save($collection, 1);
    }echo 'ok';
} catch (Throwable $error) {
    echo $error::class;
}
