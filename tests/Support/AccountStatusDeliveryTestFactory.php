<?php

namespace Tests\Support;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
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

final class AccountStatusDeliveryTestFactory
{
    public static function transportMessage(): AccountStatusDeliveryMessage
    {
        return AccountStatusDeliveryMessage::wrap(self::payload());
    }

    public static function genericMessage(): PublicProjectionDeliveryMessage
    {
        $payload = self::payload();
        $event = $payload->event;
        $source = PublicProjectionDeliverySourceModule::fromString('IdentityAccess');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('AccountStatus');
        $aggregateId = PublicProjectionDeliveryAggregateId::fromString($event->payload->accountId->value);
        $eventType = PublicProjectionDeliveryEventType::fromString($event->type->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents(
            $source,
            $aggregate,
            $aggregateId,
            $event->payload->occurredVersion,
            $index,
            $eventType,
            $version,
        );
        $occurredAt = $event->occurredAt->value;

        return new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $eventType,
            $version,
            $source,
            $aggregate,
            $aggregateId,
            new PublicProjectionDeliveryOrder($event->payload->occurredVersion, $index),
            $occurredAt,
            $occurredAt,
            $payload,
        );
    }

    private static function payload(): AccountStatusDeliveryPayload
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
            new AccountStatusActorId('operator-49j'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T15:00:00+00:00')),
            new AccountStatusIntentId('intent-49j'),
        );

        return new AccountStatusDeliveryPayload(
            (new AccountStatusEventCatalog)->eventFor($transition, $context, 8),
        );
    }
}
