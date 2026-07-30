<?php

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use App\Infrastructure\ProfessionalStatusEventRouting\PostgreSql\PostgreSqlProfessionalStatusInboxRepository;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
touch($barrier.'.ready.'.$argv[2]);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
$id = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
$version = ProfessionalStatusEventPayloadVersion::V1;
$eventId = ProfessionalStatusEventId::derive(ProfessionalStatusEventType::Suspended, $version, $id, $transition, 2);
$at = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
$event = new ProfessionalStatusEvent(new ProfessionalStatusEventMetadata(ProfessionalStatusEventType::Suspended, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new ProfessionalStatusEventPayload($eventId, $id, 'active>suspend>suspended', $transition->from, $transition->to, $transition->action, 1, 2));
$envelope = ProfessionalStatusTransportEnvelope::wrap(new ProfessionalStatusDeliveryPayload($event));
$repository = new PostgreSqlProfessionalStatusInboxRepository(PostgreSqlTestEnvironment::connection(), new ProfessionalStatusTransportSerializer);
echo $repository->store($envelope)->status->value;
