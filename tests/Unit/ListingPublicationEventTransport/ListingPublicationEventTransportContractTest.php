<?php

namespace Tests\Unit\ListingPublicationEventTransport;

use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingDiagnosticCode;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingResult;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingStatus;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventTransportException;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ListingPublicationEventTransportContractTest extends TestCase
{
    public function test_single_adapter_implements_the_existing_delivery_payload_contract(): void
    {
        $payload = new ListingPublicationDeliveryPayload($this->event());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertTrue((new ReflectionClass($payload))->isReadOnly());
        self::assertSame(['canonicalEvent' => (new ListingPublicationEventSerializer)->serialize($this->event())], $payload->fields());
    }

    public function test_round_trip_preserves_the_complete_canonical_event_exactly(): void
    {
        $payload = new ListingPublicationDeliveryPayload($this->event());
        $restored = ListingPublicationDeliveryPayload::restore($payload->fields());

        self::assertEquals($payload->event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame($payload->checksum(), $restored->checksum());
        self::assertSame($payload->event->eventId->value, $restored->event->eventId->value);
        self::assertSame($payload->event->metadata->fields(), $restored->event->metadata->fields());
    }

    public function test_checksum_is_derived_only_from_the_canonical_event(): void
    {
        $first = new ListingPublicationDeliveryPayload($this->event());
        $second = new ListingPublicationDeliveryPayload($this->event());

        self::assertSame(hash('sha256', $first->fields()['canonicalEvent']), $first->checksum());
        self::assertSame($first->checksum(), $second->checksum());
    }

    public function test_business_event_and_delivery_message_identities_remain_distinct(): void
    {
        $event = $this->event();
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents(
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString($event->payload->listingId->value),
            $event->payload->publicationVersion,
            PublicProjectionDeliveryEventIndex::fromInt(1),
            PublicProjectionDeliveryEventType::fromString($event->type->value),
            PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
        );
        $messageId = PublicProjectionDeliveryMessageId::fromIdempotencyKey($key);

        self::assertStringStartsWith('listing-publication-', $event->eventId->value);
        self::assertStringStartsWith('ppd-message:', $messageId->value);
        self::assertNotSame($event->eventId->value, $messageId->value);
        self::assertSame($event->eventId->value, ListingPublicationDeliveryPayload::restore((new ListingPublicationDeliveryPayload($event))->fields())->event->eventId->value);
    }

    #[DataProvider('invalidEnvelopeProvider')]
    public function test_invalid_or_non_canonical_transport_envelope_is_rejected(array $fields): void
    {
        $this->expectException(ListingPublicationEventTransportException::class);
        ListingPublicationDeliveryPayload::restore($fields);
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidEnvelopeProvider(): iterable
    {
        yield 'missing canonical event' => [[]];
        yield 'additional field' => [['canonicalEvent' => '{}', 'other' => 'x']];
        yield 'invalid json' => [['canonicalEvent' => '{']];
        yield 'invalid shape' => [['canonicalEvent' => '{}']];
    }

    public function test_tampered_business_identity_is_rejected(): void
    {
        $fields = (new ListingPublicationDeliveryPayload($this->event()))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['eventId'] = 'listing-publication-'.str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(ListingPublicationEventTransportException::class);
        ListingPublicationDeliveryPayload::restore($fields);
    }

    public function test_router_contract_receives_the_restored_business_event(): void
    {
        $method = new ReflectionMethod(ListingPublicationEventRouter::class, 'route');

        self::assertSame(ListingPublicationEvent::class, $method->getParameters()[0]->getType()?->getName());
        self::assertSame(ListingPublicationEventRoutingResult::class, $method->getReturnType()?->getName());
    }

    #[DataProvider('routingOutcomeProvider')]
    public function test_only_real_routing_acknowledges_delivery(ListingPublicationEventRoutingResult $result, ListingPublicationEventRoutingStatus $status, bool $acknowledged): void
    {
        self::assertSame($status, $result->status);
        self::assertSame($acknowledged, $result->acknowledgesDelivery());
    }

    /** @return iterable<string, array{ListingPublicationEventRoutingResult,ListingPublicationEventRoutingStatus,bool}> */
    public static function routingOutcomeProvider(): iterable
    {
        yield 'routed' => [ListingPublicationEventRoutingResult::routed(), ListingPublicationEventRoutingStatus::Routed, true];
        yield 'deferred' => [ListingPublicationEventRoutingResult::deferred(ListingPublicationEventRoutingDiagnosticCode::RouteUnavailable), ListingPublicationEventRoutingStatus::Deferred, false];
        yield 'retryable failure' => [ListingPublicationEventRoutingResult::retryableFailure(ListingPublicationEventRoutingDiagnosticCode::TransferFailed), ListingPublicationEventRoutingStatus::RetryableFailure, false];
        yield 'rejected' => [ListingPublicationEventRoutingResult::rejected(ListingPublicationEventRoutingDiagnosticCode::UnsupportedEvent), ListingPublicationEventRoutingStatus::Rejected, false];
    }

    private function event(): ListingPublicationEvent
    {
        $metadata = new ListingPublicationEventMetadata(
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
            ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
        );

        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            2,
            $metadata,
        )[0];
    }
}
