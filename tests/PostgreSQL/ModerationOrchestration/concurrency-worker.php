<?php

use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 5) {
    exit(2);
}
[$script, $barrier, $worker, $caseId, $reportId] = $arguments;
$number = (int) $worker;
$id = static fn (int $suffix): string => sprintf('53f10000-0000-4000-8000-%012d', $suffix);
$connection = PostgreSqlTestEnvironment::connection();
$mapper = new ModerationPersistenceMapper;
$cases = new PostgreSqlModerationCaseStore($connection, $mapper);
$queue = new DeterministicModerationQueueRuntimeV1(new PostgreSqlModerationQueueStore($connection, $mapper));
$orchestrator = new DeterministicModerationCaseOrchestratorV1(new DeterministicModerationRuntimeV1(
    $cases,
    new PostgreSqlModerationDecisionStore($connection, $mapper),
    $queue,
    new DeterministicModerationRuntimeAvailabilityPolicy(['case_store' => true, 'decision_store' => true, 'queue_store' => true]),
));

touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo $orchestrator->validate(new ValidateModerationReportV1(
    $id(20 + $number),
    $caseId,
    $reportId,
    $id(10 + $number),
    'Accepted',
    'verified',
    1,
    new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
    'v1',
))->status->value;
