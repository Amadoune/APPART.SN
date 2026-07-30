<?php

namespace App\Application\IdentityAccessEventDelivery;

use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Throwable;

final readonly class IdentityAccessDeliveryConsumer
{
    public function __construct(private IdentityAccessEventTransportSerializer $serializer) {}

    public function consume(
        IdentityAccessDeliveryMessageV1 $message,
        IdentityAccessRoutingDestination $destination,
    ): IdentityAccessConsumedFact {
        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
        } catch (Throwable $error) {
            throw new \UnexpectedValueException('Corrupted IAM delivery.', previous: $error);
        }

        if (! $this->supports($restored->event->type, $destination)) {
            throw new \UnexpectedValueException('Unsupported IAM delivery destination.');
        }

        return new IdentityAccessConsumedFact(
            $restored->messageId,
            $restored->payloadChecksum,
            $destination,
            $restored->event,
        );
    }

    private function supports(
        IdentityAccessEventType $type,
        IdentityAccessRoutingDestination $destination,
    ): bool {
        return match ($destination) {
            IdentityAccessRoutingDestination::PrivateAudit => true,
            IdentityAccessRoutingDestination::IdentitySource => in_array($type, [
                IdentityAccessEventType::ProfileEmailChanged,
                IdentityAccessEventType::ProfilePhoneChanged,
            ], true),
            IdentityAccessRoutingDestination::Notifications => in_array($type, [
                IdentityAccessEventType::ProfileEmailChanged,
                IdentityAccessEventType::ProfilePhoneChanged,
                IdentityAccessEventType::ClosureRequested,
                IdentityAccessEventType::AccountClosed,
            ], true),
            IdentityAccessRoutingDestination::SessionInvalidation => $type === IdentityAccessEventType::AccountClosed,
            IdentityAccessRoutingDestination::CrossDomainAvailability => in_array($type, [
                IdentityAccessEventType::AccountClosed,
                IdentityAccessEventType::AccountReopened,
            ], true),
        };
    }
}
