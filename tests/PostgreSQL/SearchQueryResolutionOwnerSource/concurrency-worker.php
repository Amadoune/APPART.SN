<?php

use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSource\SearchQueryResolutionOwnerSourceMapperTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$source = new PostgreSqlSearchQueryResolutionOwnerSource(PostgreSqlTestEnvironment::connection(), new SearchQueryResolutionOwnerSourceMapper);
echo $source->append(SearchQueryResolutionOwnerSourceMapperTest::state())->value;
