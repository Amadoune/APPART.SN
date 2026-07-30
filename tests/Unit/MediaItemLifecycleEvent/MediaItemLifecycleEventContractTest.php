<?php

namespace Tests\Unit\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventSerializer;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MediaItemLifecycleEventContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_catalog_mapping_payload_and_serialization_are_canonical(MediaItemLifecycleTransition $transition, MediaItemLifecycleEventType $type): void
    {
        $event = $this->event($transition, $type);
        $serialized = (new MediaItemLifecycleEventSerializer)->serialize($event);

        self::assertSame($type, (new MediaItemLifecycleEventCatalog)->typeFor($transition));
        self::assertSame($serialized, (new MediaItemLifecycleEventSerializer)->serialize($event));
        self::assertSame(['eventId', 'eventType', 'payloadVersion', 'payload', 'metadata'], array_keys($event->canonical()));
        self::assertSame(['eventId', 'aggregateType', 'mediaId', 'transition', 'previousState', 'currentState', 'action', 'version', 'occurredVersion'], array_keys($event->payload->fields()));
        foreach (['url', 'caption', 'order', 'contentChecksum', 'collectionId', 'replacement', 'primary', 'source'] as $forbidden) {
            self::assertStringNotContainsStringIgnoringCase($forbidden, $serialized);
        }
        self::assertTrue((new ReflectionClass($event))->isReadOnly());
        self::assertTrue((new ReflectionClass($event->payload))->isReadOnly());
    }

    public function test_identity_is_deterministic_versioned_and_sha256(): void
    {
        [$transition, $type] = self::transitions()[0];
        $first = $this->event($transition, $type, 2);
        $same = $this->event($transition, $type, 2);
        $next = $this->event($transition, $type, 3);
        self::assertSame($first->payload->eventId->value, $same->payload->eventId->value);
        self::assertNotSame($first->payload->eventId->value, $next->payload->eventId->value);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first->payload->eventId->value);
    }

    public function test_uncertified_transition_is_refused(): void
    {
        $this->expectException(DomainException::class);
        (new MediaItemLifecycleEventCatalog)->typeFor(
            new MediaItemLifecycleTransition(MediaItemLifecycleState::Removed, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive),
        );
    }

    public function test_metadata_refuses_recording_before_occurrence(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaItemLifecycleEventMetadata(
            MediaItemLifecycleEventType::Removed,
            MediaItemLifecycleEventPayloadVersion::V1,
            $this->actor(),
            $this->at('2026-07-23T12:00:01Z'),
            $this->at('2026-07-23T12:00:00Z'),
        );
    }

    public function test_catalog_and_versions_are_closed(): void
    {
        self::assertSame(['media.item.lifecycle.removed', 'media.item.lifecycle.archived'], array_column(MediaItemLifecycleEventType::cases(), 'value'));
        self::assertSame([1], array_column(MediaItemLifecycleEventPayloadVersion::cases(), 'value'));
    }

    /** @return list<array{MediaItemLifecycleTransition, MediaItemLifecycleEventType}> */
    public static function transitions(): array
    {
        return [
            [new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove), MediaItemLifecycleEventType::Removed],
            [new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive), MediaItemLifecycleEventType::Archived],
        ];
    }

    private function event(MediaItemLifecycleTransition $transition, MediaItemLifecycleEventType $type, int $occurredVersion = 2): MediaItemLifecycleEvent
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000050');
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $eventId = MediaItemLifecycleEventId::derive($type, $version, $id, $transition, $occurredVersion);
        $at = $this->at('2026-07-23T12:00:00.123456Z');

        return new MediaItemLifecycleEvent(
            new MediaItemLifecycleEventMetadata($type, $version, $this->actor(), $at, $at),
            new MediaItemLifecycleEventPayload(
                $eventId,
                $id,
                implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]),
                $transition->from,
                $transition->to,
                $transition->action,
                $version->value,
                $occurredVersion,
            ),
        );
    }

    private function actor(): MediaItemLifecycleActorId
    {
        return MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    }

    private function at(string $value): MediaItemLifecycleOccurredAt
    {
        return MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable($value));
    }
}
