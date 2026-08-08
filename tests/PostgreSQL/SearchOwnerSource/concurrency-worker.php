<?php

use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\SearchDiscovery\SearchOwnerSource\SearchOwnerSourceMapperTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$source = new PostgreSqlSearchOwnerSource(PostgreSqlTestEnvironment::connection(), new SearchOwnerSourceMapper);
echo $source->append(SearchOwnerSourceMapperTest::state())->value;
