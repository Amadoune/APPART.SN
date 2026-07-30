<?php

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use App\Infrastructure\MediaItemLifecycleEventRouting\PostgreSql\PostgreSqlMediaItemLifecycleInboxRepository;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    throw new RuntimeException('The media item routing worker requires a barrier and worker number.');
}
[, $barrier, $worker] = $arguments;
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
$id = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
$version = MediaItemLifecycleEventPayloadVersion::V1;
$eventId = MediaItemLifecycleEventId::derive(MediaItemLifecycleEventType::Removed, $version, $id, $transition, 2);
$at = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
$event = new MediaItemLifecycleEvent(new MediaItemLifecycleEventMetadata(MediaItemLifecycleEventType::Removed, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new MediaItemLifecycleEventPayload($eventId, $id, 'active>remove>removed', $transition->from, $transition->to, $transition->action, 1, 2));
$envelope = MediaItemLifecycleTransportEnvelope::wrap(new MediaItemLifecycleDeliveryPayload($event));
$repository = new PostgreSqlMediaItemLifecycleInboxRepository(PostgreSqlTestEnvironment::connection(), new MediaItemLifecycleTransportSerializer);
echo $repository->store($envelope)->status->value;
