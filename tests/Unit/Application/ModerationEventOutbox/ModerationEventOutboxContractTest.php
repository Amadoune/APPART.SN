<?php

namespace Tests\Unit\Application\ModerationEventOutbox;

use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ModerationEventOutboxContractTest extends TestCase
{
    #[Test]
    public function messages_deliveries_and_results_are_closed_and_immutable(): void
    {
        $time = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
        $message = new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::ReportSubmitted,
            '53b10000-0000-4000-8000-000000000001',
            1,
            [],
            'v1',
            $time,
            $time,
            '53b10000-0000-4000-8000-000000000002',
            '53b10000-0000-4000-8000-000000000003',
        ));
        $delivery = new ModerationOutboxDelivery(
            $message,
            ModerationRoutingDestination::QueueProjection,
            1,
            'worker-a',
        );

        self::assertTrue((new ReflectionClass($message))->isReadOnly());
        self::assertTrue((new ReflectionClass($delivery))->isReadOnly());
        self::assertSame($message, $delivery->message);
        self::assertSame([
            'stored',
            'already_stored',
            'divergent_message',
            'rejected',
        ], array_column(ModerationOutboxAppendResult::cases(), 'value'));
        self::assertTrue((new ReflectionClass(ModerationOutboxReaderV1::class))->hasMethod(
            'claimNextForDestination',
        ));
    }
}
