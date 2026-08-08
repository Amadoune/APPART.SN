<?php

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ContactsLeads\AntiAbuseOwnerSource\AntiAbuseOwnerSourceMapperTest;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $worker] = $argv;
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$effectiveAt = new DateTimeImmutable('2026-07-31T08:00:00Z');
$source = new PostgreSqlAntiAbuseOwnerSource(PostgreSqlTestEnvironment::connection(), new AntiAbuseOwnerSourceMapper);
echo $source->append(new AntiAbuseRevisionState(
    AntiAbuseOwnerSourceMapperTest::intent(),
    1,
    AntiAbuseRevisionDecision::Allowed,
    $effectiveAt,
    $effectiveAt->modify('+1 minute'),
    'anti-abuse-v1',
))->value;
