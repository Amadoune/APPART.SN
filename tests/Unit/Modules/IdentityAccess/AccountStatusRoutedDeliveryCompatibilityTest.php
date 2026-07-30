<?php

namespace Tests\Unit\Modules\IdentityAccess;

use App\Application\AccountStatusEventConsumption\AccountStatusDeliveryConsumer;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryRoutingProofV1;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use PHPUnit\Framework\TestCase;
use Tests\Support\AccountStatusDeliveryTestFactory;

final class AccountStatusRoutedDeliveryCompatibilityTest extends TestCase
{
    public function test_routing_proof_is_deterministic_and_bound_to_the_generic_message(): void
    {
        $message = AccountStatusDeliveryTestFactory::genericMessage();
        $destination = PublicProjectionDeliveryDestination::fromString(
            AccountStatusRoutingDestination::LifecycleFacts->value,
        );
        $first = PublicProjectionRoutedDeliveryMessageV1::fromDecision($message, $destination);
        $same = PublicProjectionRoutedDeliveryMessageV1::fromDecision($message, $destination);

        self::assertTrue($first->hasValidRoutingProof());
        self::assertSame($first->routingProof->checksum, $same->routingProof->checksum);
        self::assertSame(1, $first->routingProof->routingVersion);
        self::assertSame($message, $first->deliveryMessage);
        self::assertNotSame(
            AccountStatusDeliveryTestFactory::transportMessage()->messageId->value,
            $message->messageId->value,
        );
        self::assertNotSame($message->idempotencyKey->value, $message->messageId->value);
    }

    public function test_consumer_delegates_a_valid_routed_delivery_to_the_certified_consumption(): void
    {
        $delivery = $this->delivery();
        $result = (new AccountStatusDeliveryConsumer(
            new AccountStatusTransportSerializer,
        ))->consumeRouted($delivery);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $result);
    }

    public function test_divergent_proof_is_rejected_before_consumption(): void
    {
        $delivery = $this->delivery();
        $proof = new PublicProjectionDeliveryRoutingProofV1(
            $delivery->routingProof->messageId,
            $delivery->routingProof->sourceModule,
            $delivery->routingProof->eventType,
            $delivery->routingProof->destination,
            1,
            str_repeat('0', 64),
        );
        $divergent = new PublicProjectionRoutedDeliveryMessageV1(
            $delivery->deliveryMessage,
            $delivery->destination,
            $proof,
        );

        self::assertSame(
            PublicProjectionDeliveryConsumptionResult::DivergentPayload,
            (new AccountStatusDeliveryConsumer(
                new AccountStatusTransportSerializer,
            ))->consumeRouted($divergent),
        );
    }

    private function delivery(): PublicProjectionRoutedDeliveryMessageV1
    {
        return PublicProjectionRoutedDeliveryMessageV1::fromDecision(
            AccountStatusDeliveryTestFactory::genericMessage(),
            PublicProjectionDeliveryDestination::fromString(
                AccountStatusRoutingDestination::LifecycleFacts->value,
            ),
        );
    }
}
