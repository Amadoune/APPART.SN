<?php

namespace Tests\Unit\PropertyLifecycleEventRouting;

use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventRouting\DurablePropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventRouting\PropertyLifecycleEventDestinationResult;
use App\Application\PropertyLifecycleEventRouting\PropertyLifecycleEventDestinationStatus;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingDiagnosticCode;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DurablePropertyLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('outcomes')]
    public function test_destination_status_is_the_only_source_of_router_outcome(PropertyLifecycleEventDestinationStatus $destinationStatus, PropertyLifecycleEventRoutingStatus $routingStatus, ?PropertyLifecycleEventRoutingDiagnosticCode $diagnostic, bool $acknowledged): void
    {
        $destination = new PropertyRoutingDestinationStub($destinationStatus);
        $event = $this->event();
        $result = (new DurablePropertyLifecycleEventRouter($destination))->route($event);

        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($acknowledged, $result->acknowledgesDelivery());
        self::assertSame($event, $destination->received);
        self::assertSame(1, $destination->calls);
    }

    /** @return iterable<string, array{PropertyLifecycleEventDestinationStatus,PropertyLifecycleEventRoutingStatus,?PropertyLifecycleEventRoutingDiagnosticCode,bool}> */
    public static function outcomes(): iterable
    {
        yield 'stored' => [PropertyLifecycleEventDestinationStatus::Stored, PropertyLifecycleEventRoutingStatus::Routed, null, true];
        yield 'already stored' => [PropertyLifecycleEventDestinationStatus::AlreadyStored, PropertyLifecycleEventRoutingStatus::Routed, null, true];
        yield 'unavailable' => [PropertyLifecycleEventDestinationStatus::Unavailable, PropertyLifecycleEventRoutingStatus::Deferred, PropertyLifecycleEventRoutingDiagnosticCode::RouteUnavailable, false];
        yield 'retryable' => [PropertyLifecycleEventDestinationStatus::RetryableFailure, PropertyLifecycleEventRoutingStatus::RetryableFailure, PropertyLifecycleEventRoutingDiagnosticCode::TransferFailed, false];
        yield 'rejected' => [PropertyLifecycleEventDestinationStatus::Rejected, PropertyLifecycleEventRoutingStatus::Rejected, PropertyLifecycleEventRoutingDiagnosticCode::CorruptedEvent, false];
    }

    private function event(): PropertyLifecycleEvent
    {
        return (new PropertyLifecycleEventCatalog)->eventsFor(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            new PropertyLifecycleEventMetadata(
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
            ),
        )[0];
    }
}

final class PropertyRoutingDestinationStub implements PropertyLifecycleEventDestination
{
    public int $calls = 0;

    public ?PropertyLifecycleEvent $received = null;

    public function __construct(private readonly PropertyLifecycleEventDestinationStatus $status) {}

    public function transfer(PropertyLifecycleEvent $event): PropertyLifecycleEventDestinationResult
    {
        $this->calls++;
        $this->received = $event;

        return new PropertyLifecycleEventDestinationResult($this->status);
    }
}
