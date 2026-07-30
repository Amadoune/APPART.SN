<?php

use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\SearchDecisionFixture;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo (new PostgreSqlSearchDecisionWriter(PostgreSqlTestEnvironment::connection(), new SearchDecisionMapper))->store(SearchDecisionFixture::make())->value;
