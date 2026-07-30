<?php

namespace App\Application\PublicProjectionDelivery;

final readonly class PublicProjectionRoutedDeliveryMessageV1
{
    public function __construct(
        public PublicProjectionDeliveryMessage $deliveryMessage,
        public PublicProjectionDeliveryDestination $destination,
        public PublicProjectionDeliveryRoutingProofV1 $routingProof,
    ) {}

    public static function fromDecision(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionDeliveryDestination $destination,
    ): self {
        return new self(
            $message,
            $destination,
            PublicProjectionDeliveryRoutingProofV1::issue($message, $destination),
        );
    }

    public function hasValidRoutingProof(): bool
    {
        return $this->routingProof->isValidFor($this->deliveryMessage, $this->destination);
    }
}
