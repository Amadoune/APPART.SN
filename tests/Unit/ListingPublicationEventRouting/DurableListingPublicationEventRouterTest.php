<?php

namespace Tests\Unit\ListingPublicationEventRouting;

use App\Application\ListingPublicationEventRouting\Contract\ListingPublicationEventDestination;
use App\Application\ListingPublicationEventRouting\DurableListingPublicationEventRouter;
use App\Application\ListingPublicationEventRouting\ListingPublicationEventDestinationResult;
use App\Application\ListingPublicationEventRouting\ListingPublicationEventDestinationStatus;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingDiagnosticCode;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DurableListingPublicationEventRouterTest extends TestCase
{
    #[DataProvider('outcomeProvider')]
    public function test_destination_result_is_the_only_source_of_routing_outcome(ListingPublicationEventDestinationStatus $destinationStatus, ListingPublicationEventRoutingStatus $routingStatus, ?ListingPublicationEventRoutingDiagnosticCode $diagnostic, bool $acknowledged): void
    {
        $destination = new RoutingDestinationStub($destinationStatus);
        $event = $this->event();
        $result = (new DurableListingPublicationEventRouter($destination))->route($event);

        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($acknowledged, $result->acknowledgesDelivery());
        self::assertSame($event, $destination->received);
        self::assertSame(1, $destination->calls);
    }

    /** @return iterable<string, array{ListingPublicationEventDestinationStatus,ListingPublicationEventRoutingStatus,?ListingPublicationEventRoutingDiagnosticCode,bool}> */
    public static function outcomeProvider(): iterable
    {
        yield 'stored' => [ListingPublicationEventDestinationStatus::Stored, ListingPublicationEventRoutingStatus::Routed, null, true];
        yield 'already stored' => [ListingPublicationEventDestinationStatus::AlreadyStored, ListingPublicationEventRoutingStatus::Routed, null, true];
        yield 'unavailable' => [ListingPublicationEventDestinationStatus::Unavailable, ListingPublicationEventRoutingStatus::Deferred, ListingPublicationEventRoutingDiagnosticCode::RouteUnavailable, false];
        yield 'temporary failure' => [ListingPublicationEventDestinationStatus::RetryableFailure, ListingPublicationEventRoutingStatus::RetryableFailure, ListingPublicationEventRoutingDiagnosticCode::TransferFailed, false];
        yield 'terminal rejection' => [ListingPublicationEventDestinationStatus::Rejected, ListingPublicationEventRoutingStatus::Rejected, ListingPublicationEventRoutingDiagnosticCode::CorruptedEvent, false];
    }

    private function event(): ListingPublicationEvent
    {
        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            2,
            new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
            ),
        )[0];
    }
}

final class RoutingDestinationStub implements ListingPublicationEventDestination
{
    public int $calls = 0;

    public ?ListingPublicationEvent $received = null;

    public function __construct(private readonly ListingPublicationEventDestinationStatus $status) {}

    public function transfer(ListingPublicationEvent $event): ListingPublicationEventDestinationResult
    {
        $this->calls++;
        $this->received = $event;

        return new ListingPublicationEventDestinationResult($this->status);
    }
}
