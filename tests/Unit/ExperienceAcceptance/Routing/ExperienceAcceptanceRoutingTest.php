<?php

namespace Tests\Unit\ExperienceAcceptance\Routing;

use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxEventId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageType;
use Appart\Modules\ExperienceAcceptance\Application\Routing\DeterministicExperienceAcceptanceRouter;
use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRouteDestination;
use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRoutingStatus;
use Appart\Modules\ExperienceAcceptance\Application\Transport\ExperienceAcceptanceTransportEnvelope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceRoutingTest extends TestCase
{
    #[DataProvider('routes')]
    public function test_routes_are_static_deterministic_and_preserve_envelope(ExperienceAcceptanceOutboxMessageType $type, ExperienceAcceptanceRouteDestination $destination): void
    {
        $eventId = new ExperienceAcceptanceOutboxEventId(str_repeat('a', 64));
        $status = 'available';
        $observedAt = '2026-08-08T10:00:00.123456Z';
        $checksum = hash('sha256', implode("\n", ['experience-acceptance-outbox-v1', ExperienceAcceptanceOutboxMessage::OWNER, (string) ExperienceAcceptanceOutboxMessage::SCHEMA_VERSION, $eventId->value, $type->value, $status, $observedAt]));
        $envelope = new ExperienceAcceptanceTransportEnvelope(new ExperienceAcceptanceOutboxMessageId(str_repeat('b', 64)), $eventId, $type, $status, $observedAt, $checksum);
        $router = new DeterministicExperienceAcceptanceRouter;
        $first = $router->route($envelope);
        $second = $router->route($envelope);
        self::assertSame(ExperienceAcceptanceRoutingStatus::Routed, $first->status);
        self::assertSame($destination, $first->destination);
        self::assertSame($first->destination, $second->destination);
        self::assertSame($envelope, $first->envelope);
        self::assertSame($envelope->messageId, $first->envelope->messageId);
        self::assertSame($envelope->eventId, $first->envelope->eventId);
        self::assertSame($envelope->type, $first->envelope->type);
        self::assertSame($envelope->status, $first->envelope->status);
        self::assertSame($envelope->observedAt, $first->envelope->observedAt);
        self::assertSame($envelope->checksum, $first->envelope->checksum);
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
}
