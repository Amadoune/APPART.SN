<?php

require dirname(__DIR__, 3).'/vendor/autoload.php';

use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$time = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
$message = new ModerationDeliveryMessageV1(new ModerationEventV1(
    ModerationEventTypeV1::ReportSubmitted,
    '53b10000-0000-4000-8000-000000000001',
    1,
    ['reportId' => '53b10000-0000-4000-8000-000000000002'],
    'v1',
    $time,
    $time,
    '53b10000-0000-4000-8000-000000000003',
    '53b10000-0000-4000-8000-000000000004',
));
$outbox = new PostgreSqlModerationOutbox(
    PostgreSqlTestEnvironment::connection(),
    new ModerationEventTransportSerializer,
    new DeterministicModerationEventRouter,
);
echo $outbox->append($message)->value;
