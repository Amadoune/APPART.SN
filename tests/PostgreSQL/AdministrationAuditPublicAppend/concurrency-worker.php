<?php

use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\AdministrationAuditAppendConnection;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationAuditAppendV1;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PublicAuditAppend\AdministrationAuditAppendMapperV1;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\AdministrationAudit\AdministrationAuditPublicAppendImplementationTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[, $barrier, $worker] = $argv;
$append = new PostgreSqlAdministrationAuditAppendV1(
    new AdministrationAuditAppendConnection(PostgreSqlTestEnvironment::connection()),
    new AdministrationAuditAppendMapperV1,
);
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start')) {
    if (microtime(true) > $deadline) {
        exit(3);
    }
    usleep(1000);
}

fwrite(
    STDOUT,
    $append->append(AdministrationAuditPublicAppendImplementationTest::record())->value,
);
