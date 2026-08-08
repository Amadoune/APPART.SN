<?php

namespace Tests\Unit\ExperienceAcceptance\Consumer;

use Appart\Modules\ExperienceAcceptance\Application\Consumer\DeterministicExperienceAcceptanceConsumer;
use Appart\Modules\ExperienceAcceptance\Application\Consumer\ExperienceAcceptanceConsumptionPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Consumer\ExperienceAcceptanceConsumptionStatus;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxEventId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageType;
use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRouteDestination;
use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingResult;
use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingStatus;
use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportEnvelope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceConsumerTest extends TestCase
{
    #[DataProvider('routes')]
    public function test_seven_certified_routes_are_accepted_without_mutation(ExperienceAcceptanceOutboxMessageType $type, ExperienceAcceptanceRouteDestination $destination): void
    {
        $routing = self::routing($type, $destination);
        $consumer = new DeterministicExperienceAcceptanceConsumer(new ExperienceAcceptanceConsumptionPolicy);
        $first = $consumer->consume($routing);
        $second = $consumer->consume($routing);
        self::assertSame(ExperienceAcceptanceConsumptionStatus::Accepted, $first->status);
        self::assertSame($first->status, $second->status);
        self::assertSame($routing, $first->routing);
        self::assertSame($routing->envelope->messageId, $first->routing->envelope->messageId);
        self::assertSame($routing->envelope->eventId, $first->routing->envelope->eventId);
        self::assertSame($routing->envelope->type, $first->routing->envelope->type);
        self::assertSame($routing->envelope->status, $first->routing->envelope->status);
        self::assertSame($routing->envelope->observedAt, $first->routing->envelope->observedAt);
        self::assertSame($routing->envelope->checksum, $first->routing->envelope->checksum);
    }

    public function test_mismatched_destination_is_rejected_before_consumption(): void
    {
        $routing = self::routing(ExperienceAcceptanceOutboxMessageType::ResponsiveCompliance, ExperienceAcceptanceRouteDestination::ReleaseCandidate);
        $result = (new DeterministicExperienceAcceptanceConsumer(new ExperienceAcceptanceConsumptionPolicy))->consume($routing);
        self::assertSame(ExperienceAcceptanceConsumptionStatus::Rejected, $result->status);
        self::assertSame($routing, $result->routing);
    }

    /** @return \Generator<string, array{ExperienceAcceptanceOutboxMessageType, ExperienceAcceptanceRouteDestination}> */
    public static function routes(): \Generator
    {
        yield 'ResponsiveCompliance' => [ExperienceAcceptanceOutboxMessageType::ResponsiveCompliance, ExperienceAcceptanceRouteDestination::ResponsiveCompliance];
        yield 'AccessibilityCompliance' => [ExperienceAcceptanceOutboxMessageType::AccessibilityCompliance, ExperienceAcceptanceRouteDestination::AccessibilityCompliance];
        yield 'UserExperience' => [ExperienceAcceptanceOutboxMessageType::UserExperience, ExperienceAcceptanceRouteDestination::UserExperience];
        yield 'EndToEndReadiness' => [ExperienceAcceptanceOutboxMessageType::EndToEndReadiness, ExperienceAcceptanceRouteDestination::EndToEndReadiness];
        yield 'PerformanceReadiness' => [ExperienceAcceptanceOutboxMessageType::PerformanceReadiness, ExperienceAcceptanceRouteDestination::PerformanceReadiness];
        yield 'UserAcceptance' => [ExperienceAcceptanceOutboxMessageType::UserAcceptance, ExperienceAcceptanceRouteDestination::UserAcceptance];
        yield 'ReleaseCandidate' => [ExperienceAcceptanceOutboxMessageType::ReleaseCandidate, ExperienceAcceptanceRouteDestination::ReleaseCandidate];
    }

    private static function routing(ExperienceAcceptanceOutboxMessageType $type, ExperienceAcceptanceRouteDestination $destination): ExperienceAcceptanceRoutingResult
    {
        $eventId = new ExperienceAcceptanceOutboxEventId(str_repeat('a', 64));
        $status = 'available';
        $observedAt = '2026-08-08T10:00:00.123456Z';
        $checksum = hash('sha256', implode("\n", ['experience-acceptance-outbox-v1', ExperienceAcceptanceOutboxMessage::OWNER, (string) ExperienceAcceptanceOutboxMessage::SCHEMA_VERSION, $eventId->value, $type->value, $status, $observedAt]));
        $envelope = new ExperienceAcceptanceTransportEnvelope(new ExperienceAcceptanceOutboxMessageId(str_repeat('b', 64)), $eventId, $type, $status, $observedAt, $checksum);

        return new ExperienceAcceptanceRoutingResult(ExperienceAcceptanceRoutingStatus::Routed, $destination, $envelope);
    }
}
