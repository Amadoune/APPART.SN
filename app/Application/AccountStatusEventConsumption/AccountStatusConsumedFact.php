<?php

namespace App\Application\AccountStatusEventConsumption;

use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryMessage;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventV1;

final readonly class AccountStatusConsumedFact
{
    public function __construct(
        public string $messageId,
        public string $eventId,
        public string $payloadChecksum,
        public AccountStatusRoutingDestination $destination,
        public AccountStatusEventV1 $event,
        public AccountStatusDeliveryMessage $message,
    ) {}
}
