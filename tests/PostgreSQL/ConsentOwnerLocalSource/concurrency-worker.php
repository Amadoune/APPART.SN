<?php

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
$barrier = $arguments[1] ?? throw new RuntimeException('Missing concurrency barrier.');
$worker = $arguments[2] ?? throw new RuntimeException('Missing concurrency worker identifier.');
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$source = new PostgreSqlConsentOwnerSource(
    PostgreSqlTestEnvironment::connection(),
    new ConsentOwnerSourceMapper,
);
$effective = new DateTimeImmutable('2026-07-31T08:00:00Z');
$result = $source->append(new ConsentRevisionState(
    LeadIngressIntentId::fromString('019428b8-5d5d-7c28-8a8f-8796c8732f91'),
    1,
    ConsentRevisionDecision::Granted,
    $effective,
    $effective->modify('+1 minute'),
    'contact-v1',
));

echo $result->value;
