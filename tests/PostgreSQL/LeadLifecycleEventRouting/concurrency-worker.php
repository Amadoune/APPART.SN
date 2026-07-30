<?php

use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use App\Infrastructure\LeadLifecycleEventRouting\PostgreSql\PostgreSqlLeadLifecycleInboxRepository;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$number = $argv[2];
touch($barrier.'.ready.'.$number);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$transition = new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver);
$leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
$version = LeadLifecycleEventPayloadVersion::V1;
$eventId = LeadLifecycleEventId::derive(LeadLifecycleEventType::Delivered, $version, $leadId, $transition, 2);
$at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
$event = new LeadLifecycleEvent(
    new LeadLifecycleEventMetadata(LeadLifecycleEventType::Delivered, $version, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
    new LeadLifecycleEventPayload($eventId, $leadId, 'created>deliver>delivered', $transition->from, $transition->to, $transition->action, 1, 2),
);
$envelope = LeadLifecycleTransportEnvelope::wrap(new LeadLifecycleDeliveryPayload($event));
$repository = new PostgreSqlLeadLifecycleInboxRepository(PostgreSqlTestEnvironment::connection(), new LeadLifecycleTransportSerializer);
echo $repository->store($envelope)->status->value;
