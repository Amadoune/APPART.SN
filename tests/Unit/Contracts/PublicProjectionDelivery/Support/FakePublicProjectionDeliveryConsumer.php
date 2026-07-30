<?php

namespace Tests\Unit\Contracts\PublicProjectionDelivery\Support;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCompatibility;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrderRelation;

final class FakePublicProjectionDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    /** @var array<string, PublicProjectionDeliveryMessage> */
    private array $messages = [];

    /** @var array<string, PublicProjectionDeliveryOrder> */
    private array $orders = [];

    public function __construct(private readonly PublicProjectionDeliveryEventCatalog $catalog, public bool $sourceReady = true) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        $compatibility = $this->catalog->compatibility($message->eventType, $message->payloadVersion);
        if ($compatibility === PublicProjectionDeliveryCompatibility::UnsupportedType) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedEventType;
        }
        if ($compatibility === PublicProjectionDeliveryCompatibility::UnsupportedVersion) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedPayloadVersion;
        }
        if (! $this->catalog->accepts($message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->payload)) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        $existing = $this->messages[$message->idempotencyKey->value] ?? null;
        if ($existing !== null) {
            return $message->hasDivergentPayload($existing)
                ? PublicProjectionDeliveryConsumptionResult::DivergentPayload
                : PublicProjectionDeliveryConsumptionResult::AlreadyConsumed;
        }
        if (! $this->sourceReady) {
            return PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness;
        }

        $stream = $message->sourceModule->value.':'.$message->aggregateType->value.':'.$message->aggregateId->value;
        $previous = $this->orders[$stream] ?? null;
        if ($previous !== null) {
            $relation = $message->order->relationTo($previous, true);
            if ($relation === PublicProjectionDeliveryOrderRelation::Before) {
                return PublicProjectionDeliveryConsumptionResult::RejectedObsolete;
            }
            if ($relation === PublicProjectionDeliveryOrderRelation::Gap) {
                return PublicProjectionDeliveryConsumptionResult::BlockedBySequenceGap;
            }
        }

        $this->messages[$message->idempotencyKey->value] = $message;
        $this->orders[$stream] = $message->order;

        return PublicProjectionDeliveryConsumptionResult::Consumed;
    }
}
