<?php

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 4) {
    throw new RuntimeException('The media item orchestration worker requires a barrier, worker number and actor.');
}
[, $barrier, $worker, $actor] = $arguments;
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$pdo = PostgreSqlTestEnvironment::connection();
$workflowMapper = new MediaItemLifecycleWorkflowMapper;
$historical = new PostgreSqlMediaItemLifecycleWorkflowRepository($pdo, $workflowMapper);
$store = new PostgreSqlMediaItemLifecycleContextualTransitionRepository($pdo, $historical, $workflowMapper, new MediaItemLifecycleContextMapper);
$inspector = new PostgreSqlMediaItemLifecycleContextualReplayInspector($pdo, $workflowMapper);
$orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, $inspector, new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);
$id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000049');
$context = new MediaItemLifecycleTransitionContext(
    MediaItemLifecycleContextVersion::V1,
    MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
    MediaId::fromString($id->value),
    new MediaItemLifecycleExpectedVersion(1),
    new MediaCollectionDecisionVersion(7),
    MediaItemLifecycleActorId::fromString($actor),
    MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00.123456Z')),
    MediaCollectionTransitionDecision::notPrimary(),
);
echo $orchestrator->execute(new MediaItemLifecycleTransitionRequest($id, MediaItemLifecycleAction::Remove, $context))->status->value;
