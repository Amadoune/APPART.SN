<?php

namespace App\Application\AccountStatusEventConsumption;

use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionRoutedDeliveryConsumerV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Throwable;

final readonly class AccountStatusDeliveryConsumer implements PublicProjectionRoutedDeliveryConsumerV1
{
    public function __construct(private AccountStatusTransportSerializer $serializer) {}

    public function consume(
        AccountStatusDeliveryMessage $message,
        AccountStatusRoutingDestination $destination,
    ): AccountStatusConsumptionResult {
        if (! in_array($message->messageType, [
            AccountStatusEventType::Suspended->value,
            AccountStatusEventType::Reactivated->value,
        ], true)) {
            return AccountStatusConsumptionResult::rejected(
                AccountStatusConsumptionDiagnostic::UnsupportedMessage,
            );
        }

        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
            if (
                $restored->messageId->value !== $message->messageId->value
                || $restored->metadata->eventId !== $message->metadata->eventId
                || $restored->payload->checksum() !== $message->payload->checksum()
                || $restored->payload->fields() !== $message->payload->fields()
            ) {
                return AccountStatusConsumptionResult::rejected(
                    AccountStatusConsumptionDiagnostic::CorruptedMessage,
                );
            }
        } catch (Throwable) {
            return AccountStatusConsumptionResult::rejected(
                AccountStatusConsumptionDiagnostic::CorruptedMessage,
            );
        }

        return AccountStatusConsumptionResult::consumed(new AccountStatusConsumedFact(
            $message->messageId->value,
            $message->metadata->eventId,
            $message->metadata->payloadChecksum->value,
            $destination,
            $restored->payload->event,
            $message,
        ));
    }

    public function consumeRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
    ): PublicProjectionDeliveryConsumptionResult {
        if (! $delivery->hasValidRoutingProof()) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        $message = $delivery->deliveryMessage;
        if (! $message->payload instanceof AccountStatusDeliveryPayload
            || $message->sourceModule->value !== 'IdentityAccess'
            || $message->aggregateType->value !== 'AccountStatus'
            || $message->payloadVersion->value !== 1) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        $event = $message->payload->event;
        if ($message->eventType->value !== $event->type->value
            || $message->aggregateId->value !== $event->payload->accountId->value
            || $message->order->aggregateVersion !== $event->payload->occurredVersion
            || $message->order->eventIndex->value !== 1) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        $destination = AccountStatusRoutingDestination::tryFrom($delivery->destination->value);
        if ($destination === null) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        $result = $this->consume(AccountStatusDeliveryMessage::wrap($message->payload), $destination);

        return match ($result->status) {
            AccountStatusConsumptionStatus::Consumed => PublicProjectionDeliveryConsumptionResult::Consumed,
            AccountStatusConsumptionStatus::Rejected => match ($result->diagnostic) {
                AccountStatusConsumptionDiagnostic::UnsupportedMessage => PublicProjectionDeliveryConsumptionResult::UnsupportedEventType,
                AccountStatusConsumptionDiagnostic::CorruptedMessage,
                AccountStatusConsumptionDiagnostic::UnsupportedDestination,
                null => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
            },
        };
    }
}
