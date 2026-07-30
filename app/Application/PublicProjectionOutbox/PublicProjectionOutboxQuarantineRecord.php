<?php

namespace App\Application\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;

final readonly class PublicProjectionOutboxQuarantineRecord
{
    public function __construct(
        public PublicProjectionDeliveryMessageId $messageId,
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionOutboxQuarantineDecision $decision,
        public PublicProjectionOutboxAttemptCount $attempts,
    ) {}
}
