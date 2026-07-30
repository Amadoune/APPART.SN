<?php

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceLifecycleWorkflowStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$source = PlaceId::fromString('10000000-0000-4000-8000-000000000001');
$target = PlaceId::fromString('10000000-0000-4000-8000-000000000002');
$context = new PlaceMergeContextV1(
    sourceId: $source,
    targetId: $target,
    expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
    observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
    observedTargetState: PlaceMergeObservedState::Enabled,
    observedSourceType: PlaceType::City,
    observedTargetType: PlaceType::City,
    observedSourceCountry: CountryCode::fromString('SN'),
    observedTargetCountry: CountryCode::fromString('SN'),
    actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
    occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
    intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
);
$store = new PostgreSqlPlaceLifecycleWorkflowStore(
    PostgreSqlTestEnvironment::connection(),
    new PlaceLifecycleWorkflowMapper,
);

echo $store->append(
    new PlaceLifecycleTransition(PlaceLifecycleState::Enabled, PlaceLifecycleAction::Merge, PlaceLifecycleState::Merged),
    $context,
)->value;
