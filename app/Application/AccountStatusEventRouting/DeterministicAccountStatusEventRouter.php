<?php

namespace App\Application\AccountStatusEventRouting;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Throwable;

final readonly class DeterministicAccountStatusEventRouter implements AccountStatusEventRouter
{
    public function __construct(private AccountStatusTransportSerializer $serializer) {}

    public function route(AccountStatusDeliveryMessage $message): AccountStatusRoutingResult
    {
        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
            if (
                $restored->messageId->value !== $message->messageId->value
                || $restored->metadata->eventId !== $message->metadata->eventId
                || $restored->payload->fields() !== $message->payload->fields()
            ) {
                return AccountStatusRoutingResult::rejected(
                    $message,
                    AccountStatusRoutingDiagnostic::CorruptedMessage,
                );
            }
        } catch (Throwable) {
            return AccountStatusRoutingResult::rejected(
                $message,
                AccountStatusRoutingDiagnostic::CorruptedMessage,
            );
        }

        return match ($message->messageType) {
            AccountStatusEventType::Suspended->value,
            AccountStatusEventType::Reactivated->value => AccountStatusRoutingResult::routed(
                $message,
                AccountStatusRoutingDestination::LifecycleFacts,
            ),
            default => AccountStatusRoutingResult::rejected(
                $message,
                AccountStatusRoutingDiagnostic::UnsupportedMessage,
            ),
        };
    }
}
