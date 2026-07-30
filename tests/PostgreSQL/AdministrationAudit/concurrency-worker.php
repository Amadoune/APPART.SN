<?php

use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\AdministrationAudit\FakeAdministrativeActionRegistryHarness;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $mode, $barrier, $worker] = $argv;
$connection = PostgreSqlTestEnvironment::connection();
$repository = new PostgreSqlAdministrativeActionRepository($connection, new AdministrativeActionMapper);
$fixtures = new FakeAdministrativeActionRegistryHarness;
$action = $mode === 'save' ? $repository->find($fixtures->primaryId()) : $fixtures->minimalAction();
if ($action === null) {
    fwrite(STDOUT, 'missing');
    exit(2);
}
if ($mode === 'save') {
    $fixtures->mutate($action);
}
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start')) {
    if (microtime(true) > $deadline) {
        fwrite(STDOUT, 'barrier-timeout');
        exit(3);
    }
    usleep(1000);
}

try {
    $mode === 'save' ? $repository->save($action, 0) : $repository->add($action);
    fwrite(STDOUT, 'ok');
} catch (AdministrativeActionIdentityConflict|ConcurrentAdministrativeActionModification $error) {
    fwrite(STDOUT, $error::class);
}
