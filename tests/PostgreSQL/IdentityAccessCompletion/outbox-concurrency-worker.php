<?php

use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use App\Infrastructure\IdentityAccessEventOutbox\PostgreSql\PostgreSqlIdentityAccessOutbox;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$event = new IdentityAccessEventV1(
    IdentityAccessEventType::ProfileNameChanged,
    AccountId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
    1,
    new DateTimeImmutable('2026-07-27T10:00:00Z'),
    new DateTimeImmutable('2026-07-27T10:00:01Z'),
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
    'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
);
$serializer = new IdentityAccessEventTransportSerializer;
$outbox = new PostgreSqlIdentityAccessOutbox(PostgreSqlTestEnvironment::connection(), $serializer);

echo $outbox->append(
    new IdentityAccessDeliveryMessageV1($event),
    [IdentityAccessRoutingDestination::PrivateAudit],
)->value;
