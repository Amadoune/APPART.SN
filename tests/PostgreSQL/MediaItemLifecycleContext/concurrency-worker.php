<?php

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 4) {
    throw new RuntimeException('The contextual media item worker requires a barrier, worker number and actor.');
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
$id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000034');
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
echo $store->append(new MediaItemLifecycleContextualAppend(
    $id,
    new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove),
    $context,
))->value;
