<?php

namespace App\Application\IdentityAccessEventRouting;

use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Throwable;

final readonly class DeterministicIdentityAccessEventRouter
{
    public function __construct(private IdentityAccessEventTransportSerializer $serializer) {}

    public function route(IdentityAccessDeliveryMessageV1 $message): IdentityAccessRoutingResult
    {
        try {
            $restored = $this->serializer->restore($this->serializer->serialize($message));
            if ($restored->fields() !== $message->fields()) {
                return IdentityAccessRoutingResult::rejected($message, 'corrupted_message');
            }
        } catch (Throwable) {
            return IdentityAccessRoutingResult::rejected($message, 'corrupted_message');
        }

        $audit = IdentityAccessRoutingDestination::PrivateAudit;
        $notifications = IdentityAccessRoutingDestination::Notifications;

        return IdentityAccessRoutingResult::routed($message, match ($message->event->type) {
            IdentityAccessEventType::ProfileNameChanged => [$audit],
            IdentityAccessEventType::ProfileEmailChanged,
            IdentityAccessEventType::ProfilePhoneChanged => [
                $audit,
                IdentityAccessRoutingDestination::IdentitySource,
                $notifications,
            ],
            IdentityAccessEventType::ClosureRequested => [$audit, $notifications],
            IdentityAccessEventType::AccountClosed => [
                $audit,
                $notifications,
                IdentityAccessRoutingDestination::SessionInvalidation,
                IdentityAccessRoutingDestination::CrossDomainAvailability,
            ],
            IdentityAccessEventType::AccountReopened => [
                $audit,
                IdentityAccessRoutingDestination::CrossDomainAvailability,
            ],
        });
    }
}
