<?php

use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Infrastructure\ModerationEventDelivery\PostgreSql\PostgreSqlModerationDeliveryStore;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';
[$script, $barrier, $worker] = $_SERVER['argv'];
$id = static fn (int $suffix): string => sprintf('53e10000-0000-4000-8000-%012d', $suffix);
$time = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
$event = new ModerationEventV1(
    ModerationEventTypeV1::DecisionIssued, $id(10), 1,
    ['decisionId' => $id(11), 'disposition' => 'Confirmed'],
    'v1', $time, $time, $id(12), $id(13),
);
$store = new PostgreSqlModerationDeliveryStore(PostgreSqlTestEnvironment::connection());
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
echo $store->deliver(new ModerationDeliveryMessageV1($event), ModerationRoutingDestination::CaseTimeline, $time)->value;
