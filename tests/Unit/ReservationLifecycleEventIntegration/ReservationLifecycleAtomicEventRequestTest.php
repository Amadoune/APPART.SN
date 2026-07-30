<?php

namespace Tests\Unit\ReservationLifecycleEventIntegration;

use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReservationLifecycleAtomicEventRequestTest extends TestCase
{
    public function test_request_is_immutable_and_preserves_explicit_delivery_instants(): void
    {
        $occurredAt = new DateTimeImmutable('2026-07-21T10:00:00.000000Z');
        $recordedAt = new DateTimeImmutable('2026-07-21T10:00:01.000000Z');
        $request = new ReservationLifecycleAtomicEventRequest(ReservationId::fromString('22222222-2222-4222-8222-222222222222'), ReservationLifecycleAction::Submit, 1, $occurredAt, $recordedAt);

        self::assertTrue((new ReflectionClass($request))->isReadOnly());
        self::assertSame($occurredAt, $request->occurredAt);
        self::assertSame($recordedAt, $request->recordedAt);
        self::assertSame(1, $request->transitionRequest()->expectedVersion);
    }

    public function test_non_positive_expected_version_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ReservationLifecycleAtomicEventRequest(ReservationId::fromString('22222222-2222-4222-8222-222222222222'), ReservationLifecycleAction::Submit, 0, new DateTimeImmutable, new DateTimeImmutable);
    }
}
