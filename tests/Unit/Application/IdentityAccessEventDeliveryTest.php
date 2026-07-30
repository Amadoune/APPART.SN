<?php

namespace Tests\Unit\Application;

use App\Application\IdentityAccessEventDelivery\IdentityAccessDeliveryConsumer;
use App\Application\IdentityAccessEventDelivery\IdentityAccessDeliveryOutcome;
use App\Application\IdentityAccessEventDelivery\IdentityAccessReplayRetryPolicy;
use App\Application\IdentityAccessEventDelivery\IdentityAccessRetryDecision;
use App\Application\IdentityAccessEventRouting\DeterministicIdentityAccessEventRouter;
use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportException;
use App\Application\IdentityAccessEventTransport\IdentityAccessEventTransportSerializer;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventCatalog;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventType;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessEventDeliveryTest extends TestCase
{
    /** @return iterable<string, array{IdentityAccessEventType, list<IdentityAccessRoutingDestination>}> */
    public static function routes(): iterable
    {
        $audit = IdentityAccessRoutingDestination::PrivateAudit;
        $notifications = IdentityAccessRoutingDestination::Notifications;
        yield 'name' => [IdentityAccessEventType::ProfileNameChanged, [$audit]];
        yield 'email' => [IdentityAccessEventType::ProfileEmailChanged, [$audit, IdentityAccessRoutingDestination::IdentitySource, $notifications]];
        yield 'phone' => [IdentityAccessEventType::ProfilePhoneChanged, [$audit, IdentityAccessRoutingDestination::IdentitySource, $notifications]];
        yield 'requested' => [IdentityAccessEventType::ClosureRequested, [$audit, $notifications]];
        yield 'closed' => [IdentityAccessEventType::AccountClosed, [$audit, $notifications, IdentityAccessRoutingDestination::SessionInvalidation, IdentityAccessRoutingDestination::CrossDomainAvailability]];
        yield 'reopened' => [IdentityAccessEventType::AccountReopened, [$audit, IdentityAccessRoutingDestination::CrossDomainAvailability]];
    }

    #[DataProvider('routes')]
    #[Test]
    public function event_is_canonical_routed_and_consumable_without_pii(
        IdentityAccessEventType $type,
        array $destinations,
    ): void {
        $serializer = new IdentityAccessEventTransportSerializer;
        $message = new IdentityAccessDeliveryMessageV1($this->event($type));
        $serialized = $serializer->serialize($message);
        $restored = $serializer->restore($serialized);
        $routing = (new DeterministicIdentityAccessEventRouter($serializer))->route($message);

        self::assertSame($message->fields(), $restored->fields());
        self::assertTrue($routing->routed);
        self::assertSame($destinations, $routing->destinations);
        self::assertNull($routing->diagnostic);
        foreach (['email@example', '+221', 'password', 'token', 'sessionId', 'reason'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $serialized);
        }

        $consumer = new IdentityAccessDeliveryConsumer($serializer);
        foreach ($destinations as $destination) {
            $fact = $consumer->consume($message, $destination);
            self::assertSame($message->messageId, $fact->messageId);
            self::assertSame($message->payloadChecksum, $fact->payloadChecksum);
            self::assertSame($destination, $fact->destination);
        }
    }

    #[Test]
    public function catalog_is_closed_and_tampering_is_rejected(): void
    {
        self::assertSame(IdentityAccessEventType::cases(), (new IdentityAccessEventCatalog)->events());
        $serializer = new IdentityAccessEventTransportSerializer;
        $serialized = $serializer->serialize(new IdentityAccessDeliveryMessageV1(
            $this->event(IdentityAccessEventType::AccountClosed),
        ));

        $this->expectException(IdentityAccessEventTransportException::class);
        $serializer->restore(str_replace('"aggregateVersion":7', '"aggregateVersion":8', $serialized));
    }

    #[Test]
    public function replay_and_retry_policy_is_closed_and_bounded(): void
    {
        $policy = new IdentityAccessReplayRetryPolicy;
        self::assertSame(IdentityAccessRetryDecision::Complete, $policy->decide(IdentityAccessDeliveryOutcome::Consumed, 1));
        self::assertSame(IdentityAccessRetryDecision::Complete, $policy->decide(IdentityAccessDeliveryOutcome::AlreadyConsumed, 4));
        self::assertSame(IdentityAccessRetryDecision::Retry, $policy->decide(IdentityAccessDeliveryOutcome::TransientFailure, 4));
        self::assertSame(IdentityAccessRetryDecision::Quarantine, $policy->decide(IdentityAccessDeliveryOutcome::TransientFailure, 5));
        self::assertSame(IdentityAccessRetryDecision::Quarantine, $policy->decide(IdentityAccessDeliveryOutcome::PermanentFailure, 1));
        self::assertSame(IdentityAccessRetryDecision::Quarantine, $policy->decide(IdentityAccessDeliveryOutcome::DivergentReplay, 1));
    }

    private function event(IdentityAccessEventType $type): IdentityAccessEventV1
    {
        return new IdentityAccessEventV1(
            $type,
            AccountId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            7,
            new DateTimeImmutable('2026-07-27T10:00:00Z'),
            new DateTimeImmutable('2026-07-27T10:00:01Z'),
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        );
    }
}
