<?php

namespace Tests\Unit\Modules\Geography;

use App\Application\PlaceLifecycleEventRouting\DurablePlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStore;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStoreResult;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStoreStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingDiagnostic;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DurablePlaceLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('storeMappings')]
    public function test_store_results_are_mapped_exhaustively(
        PlaceLifecycleInboxStoreStatus $stored,
        PlaceLifecycleEventRoutingStatus $routed,
        ?PlaceLifecycleEventRoutingDiagnostic $diagnostic,
    ): void {
        $store = new class($stored) implements PlaceLifecycleInboxStore
        {
            public function __construct(private PlaceLifecycleInboxStoreStatus $status) {}

            public function store(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleInboxStoreResult
            {
                return new PlaceLifecycleInboxStoreResult($this->status);
            }
        };

        $result = (new DurablePlaceLifecycleEventRouter($store))->route(self::envelope());

        self::assertSame($routed, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($routed === PlaceLifecycleEventRoutingStatus::Routed, $result->acknowledgesDelivery());
    }

    public function test_technical_failure_is_closed_as_retryable(): void
    {
        $store = new class implements PlaceLifecycleInboxStore
        {
            public function store(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleInboxStoreResult
            {
                throw new RuntimeException('technical');
            }
        };

        $result = (new DurablePlaceLifecycleEventRouter($store))->route(self::envelope());

        self::assertSame(PlaceLifecycleEventRoutingStatus::RetryableFailure, $result->status);
        self::assertSame(PlaceLifecycleEventRoutingDiagnostic::TransferFailed, $result->diagnostic);
    }

    /** @return list<array{PlaceLifecycleInboxStoreStatus,PlaceLifecycleEventRoutingStatus,?PlaceLifecycleEventRoutingDiagnostic}> */
    public static function storeMappings(): array
    {
        return [
            [PlaceLifecycleInboxStoreStatus::Stored, PlaceLifecycleEventRoutingStatus::Routed, null],
            [PlaceLifecycleInboxStoreStatus::AlreadyStored, PlaceLifecycleEventRoutingStatus::Routed, null],
            [PlaceLifecycleInboxStoreStatus::Unavailable, PlaceLifecycleEventRoutingStatus::Deferred, PlaceLifecycleEventRoutingDiagnostic::RouteUnavailable],
            [PlaceLifecycleInboxStoreStatus::RetryableFailure, PlaceLifecycleEventRoutingStatus::RetryableFailure, PlaceLifecycleEventRoutingDiagnostic::TransferFailed],
            [PlaceLifecycleInboxStoreStatus::Rejected, PlaceLifecycleEventRoutingStatus::Rejected, PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent],
        ];
    }

    public static function envelope(): PlaceLifecycleTransportEnvelope
    {
        $context = new PlaceMergeContextV1(
            PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
            new PlaceMergeExpectedSourceVersion(7),
            new PlaceMergeObservedTargetVersion(11),
            PlaceMergeObservedState::Enabled,
            PlaceType::City,
            PlaceType::City,
            CountryCode::fromString('SN'),
            CountryCode::fromString('SN'),
            PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
        $transition = new PlaceLifecycleTransition(
            PlaceLifecycleState::Enabled,
            PlaceLifecycleAction::Merge,
            PlaceLifecycleState::Merged,
        );
        $event = (new PlaceLifecycleEventCatalog)->eventFor($transition, $context, 8);

        return PlaceLifecycleTransportEnvelope::wrap(new PlaceLifecycleDeliveryPayload($event));
    }
}
