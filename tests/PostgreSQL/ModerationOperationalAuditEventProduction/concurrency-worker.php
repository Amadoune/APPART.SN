<?php

use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditOutboxAppender;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
$barrier = is_string($arguments[1] ?? null) ? $arguments[1] : '';
$worker = is_string($arguments[2] ?? null) ? $arguments[2] : '';
if ($barrier === '' || $worker === '') {
    exit(2);
}
$id = static fn (int $suffix): string => sprintf('53f30000-0000-4000-8000-%012d', $suffix);
$now = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
$message = new ModerationOperationalAuditOutboxMessageV1(new FindingRecordedEventV1(
    $id(50), $id(51), 1, 'v1', $now, $now, $id(52), $id(53),
));
$appender = new PostgreSqlModerationOperationalAuditOutboxAppender(
    PostgreSqlTestEnvironment::connection(),
);
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start')) {
    if (microtime(true) > $deadline) {
        exit(3);
    }
    usleep(1000);
}

fwrite(STDOUT, $appender->append($message)->value);
