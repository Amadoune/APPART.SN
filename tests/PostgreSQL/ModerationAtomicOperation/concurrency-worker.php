<?php

require dirname(__DIR__, 3).'/vendor/autoload.php';

use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationOutboxAppender;
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
    '53a10000-0000-4000-8000-000000000777',
    1,
    ['reportId' => '53a10000-0000-4000-8000-000000000778'],
    'v1',
    $time,
    $time,
    '53a10000-0000-4000-8000-000000000779',
    '53a10000-0000-4000-8000-000000000780',
));
$connection = PostgreSqlTestEnvironment::connection();
$appender = new PostgreSqlModerationOutboxAppender($connection, new ModerationEventTransportSerializer);
$transaction = new PostgreSqlModerationAtomicOperation($connection);
$result = $transaction->execute(
    static fn (): ModerationAtomicWorkResult => ModerationAtomicWorkResult::commit($appender->append($message)),
);
echo $result->value->value;
