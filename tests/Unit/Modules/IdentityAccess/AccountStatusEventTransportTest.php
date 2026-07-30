<?php

namespace Tests\Unit\Modules\IdentityAccess;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AccountStatusEventTransport\AccountStatusEventTransportException;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\AccountStatusEventTransport\AccountStatusTransportVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AccountStatusEventTransportTest extends TestCase
{
    public function test_event_round_trips_byte_for_byte(): void
    {
        $message = self::message();
        $serializer = new AccountStatusTransportSerializer;
        $bytes = $serializer->serialize($message);
        $restored = $serializer->restore($bytes);

        self::assertSame($bytes, $serializer->serialize($restored));
        self::assertSame($message->payload->fields(), $restored->payload->fields());
        self::assertSame($message->payload->checksum(), $restored->payload->checksum());
    }

    public function test_business_and_transport_identities_are_distinct_and_deterministic(): void
    {
        $message = self::message();

        self::assertMatchesRegularExpression('/^account-status-delivery-[a-f0-9]{64}$/', $message->messageId->value);
        self::assertNotSame($message->metadata->eventId, $message->messageId->value);
        self::assertSame($message->payload->event->eventId->value, $message->metadata->eventId);
        self::assertSame(AccountStatusTransportVersion::V1, $message->transportVersion);
    }

    public function test_shape_and_metadata_are_closed(): void
    {
        $decoded = json_decode(
            (new AccountStatusTransportSerializer)->serialize(self::message()),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'eventId', 'payloadChecksum'], array_keys($decoded['metadata']));
    }

    public function test_non_canonical_transport_is_rejected(): void
    {
        $serializer = new AccountStatusTransportSerializer;
        $bytes = $serializer->serialize(self::message());

        $this->expectException(AccountStatusEventTransportException::class);
        $serializer->restore(str_replace('{"messageId"', '{ "messageId"', $bytes));
    }

    public function test_tampered_business_event_is_rejected(): void
    {
        $fields = self::message()->payload->fields();
        $event = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $event['payload']['occurredVersion'] = 9;
        $fields['canonicalEvent'] = json_encode(
            $event,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        $this->expectException(AccountStatusEventTransportException::class);
        AccountStatusDeliveryPayload::restore($fields);
    }

    private static function message(): AccountStatusDeliveryMessage
    {
        $transition = new AccountStatusTransition(
            AccountStatusState::Active,
            AccountStatusAction::Suspend,
            AccountStatusState::Suspended,
        );
        $context = new AccountStatusContextV1(
            AccountId::fromString('49f00000-0000-4000-8000-000000000001'),
            $transition->from,
            new AccountStatusVersion(7),
            new AccountStatusVersion(7),
            $transition->action,
            new AccountStatusActorId('operator-49g'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T12:00:00+00:00')),
            new AccountStatusIntentId('intent-49g'),
        );
        $event = (new AccountStatusEventCatalog)->eventFor($transition, $context, 8);

        return AccountStatusDeliveryMessage::wrap(new AccountStatusDeliveryPayload($event));
    }
}
