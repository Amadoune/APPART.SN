<?php

use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
$outbox = new PostgreSqlModerationOutbox(
    PostgreSqlTestEnvironment::connection(),
    new ModerationEventTransportSerializer,
    new DeterministicModerationEventRouter,
);

touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

echo $outbox->claimNextForDestination(
    'listing-'.$worker,
    ModerationRoutingDestination::ListingHandoff,
    new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
)?->message->messageId ?? 'none';
