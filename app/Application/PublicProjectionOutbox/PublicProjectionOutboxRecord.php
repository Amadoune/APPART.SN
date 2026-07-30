<?php

namespace App\Application\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use InvalidArgumentException;

final readonly class PublicProjectionOutboxRecord
{
    public function __construct(
        public PublicProjectionDeliveryMessage $message,
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionDeliveryStatus $status,
        public PublicProjectionOutboxAttemptCount $attempts,
        public PublicProjectionOutboxClaimState $claimState,
        public ?PublicProjectionOutboxLease $lease = null,
        public ?PublicProjectionOutboxRetryDecision $retryDecision = null,
        public ?PublicProjectionOutboxQuarantineRecord $quarantine = null,
        public ?PublicProjectionRoutedDeliveryMessageV1 $routedDelivery = null,
    ) {
        if (($status === PublicProjectionDeliveryStatus::Claimed) !== ($claimState === PublicProjectionOutboxClaimState::Claimed && $lease !== null)) {
            throw new InvalidArgumentException('Claimed Outbox records require exactly one active lease.');
        }
        if ($status === PublicProjectionDeliveryStatus::Quarantined && $quarantine === null) {
            throw new InvalidArgumentException('Quarantined Outbox records require a quarantine decision.');
        }
        if ($status === PublicProjectionDeliveryStatus::RetryScheduled && $retryDecision === null) {
            throw new InvalidArgumentException('Retry Outbox records require a retry decision.');
        }
    }

    public static function pending(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): self
    {
        return new self($message, $consumerId, PublicProjectionDeliveryStatus::Pending, PublicProjectionOutboxAttemptCount::fromInt(0), PublicProjectionOutboxClaimState::Unclaimed);
    }

    public static function pendingRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): self {
        return new self(
            $delivery->deliveryMessage,
            $consumerId,
            PublicProjectionDeliveryStatus::Pending,
            PublicProjectionOutboxAttemptCount::fromInt(0),
            PublicProjectionOutboxClaimState::Unclaimed,
            routedDelivery: $delivery,
        );
    }
}
