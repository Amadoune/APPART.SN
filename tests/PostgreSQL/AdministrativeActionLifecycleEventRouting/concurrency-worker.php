<?php

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use App\Infrastructure\AdministrativeActionLifecycleEventRouting\PostgreSql\PostgreSqlAdministrativeActionLifecycleInboxRepository;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    throw new RuntimeException('The Administrative Action routing worker requires a barrier and worker number.');
}

[, $barrier, $worker] = $arguments;
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

$transition = new AdministrativeActionLifecycleTransition(
    AdministrativeActionLifecycleState::Draft,
    AdministrativeActionLifecycleState::Recorded,
    AdministrativeActionLifecycleAction::Record,
);
$actionId = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
$version = AdministrativeActionLifecycleEventPayloadVersion::V1;
$eventId = AdministrativeActionLifecycleEventId::derive(AdministrativeActionLifecycleEventType::Recorded, $version, $actionId, $transition, 2);
$at = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00+00:00'));
$event = new AdministrativeActionLifecycleEvent(
    new AdministrativeActionLifecycleEventMetadata(AdministrativeActionLifecycleEventType::Recorded, $version, ActorId::fromString('decision-actor-001'), $at, $at),
    new AdministrativeActionLifecycleEventPayload($eventId, $actionId, 'draft>record>recorded', $transition->from, $transition->to, $transition->action, 1, 2),
);
$envelope = AdministrativeActionLifecycleTransportEnvelope::wrap(new AdministrativeActionLifecycleDeliveryPayload($event));
$repository = new PostgreSqlAdministrativeActionLifecycleInboxRepository(
    PostgreSqlTestEnvironment::connection(),
    new AdministrativeActionLifecycleTransportSerializer,
);

echo $repository->store($envelope)->status->value;
