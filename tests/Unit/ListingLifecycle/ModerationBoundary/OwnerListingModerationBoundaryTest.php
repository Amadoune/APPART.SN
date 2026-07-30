<?php

namespace Tests\Unit\ListingLifecycle\ModerationBoundary;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentStateV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationStoredState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OwnerListingModerationBoundaryTest extends TestCase
{
    #[Test]
    public function reader_maps_owner_workflow_to_closed_eligibility_without_exposing_state(): void
    {
        $listingId = $this->listingId();
        $now = $this->now();

        self::assertSame(
            ListingModerationEligibilityV1::Eligible,
            (new OwnerListingModerationReaderV1(
                BoundaryWorkflowStoreStub::found($listingId, ListingPublicationState::Published, 4),
                new ListingPublicationWorkflow,
            ))->read($listingId, $now),
        );
        self::assertSame(
            ListingModerationEligibilityV1::Ineligible,
            (new OwnerListingModerationReaderV1(
                BoundaryWorkflowStoreStub::found($listingId, ListingPublicationState::Archived, 9),
                new ListingPublicationWorkflow,
            ))->read($listingId, $now),
        );
    }

    #[Test]
    public function gateway_uses_only_snapshot_version_and_maps_applied_result(): void
    {
        $orchestrator = $this->createMock(ListingPublicationOrchestrator::class);
        $orchestrator->expects(self::once())
            ->method('transition')
            ->with(self::callback(
                fn (ListingPublicationOrchestrationRequest $request): bool => $request->expectedVersion === 7
                    && $request->action === ListingPublicationAction::Suspend,
            ))
            ->willReturn(ListingPublicationOrchestrationResult::applied(
                new ListingPublicationTransition(
                    ListingPublicationState::Published,
                    ListingPublicationState::Suspended,
                    ListingPublicationAction::Suspend,
                ),
            ));
        $intents = new BoundaryIntentStoreStub(ListingModerationIntentReservationV1::Reserved);

        $result = (new OwnerListingModerationCommandGatewayV1(
            $intents,
            new BoundaryTransactionStub,
            $orchestrator,
            BoundaryWorkflowStoreStub::found($this->listingId(), ListingPublicationState::Published, 7),
        ))->apply(
            $this->listingId(),
            ListingModerationActionV1::Suspend,
            '53c30000-0000-4000-8000-000000000101',
            str_repeat('a', 64),
            $this->now(),
        );

        self::assertSame(ListingModerationCommandResultV1::Applied, $result);
        self::assertSame(ListingModerationCommandResultV1::Applied, $intents->completed);
    }

    #[Test]
    public function gateway_closes_idempotence_and_divergence_before_transition(): void
    {
        $orchestrator = $this->createMock(ListingPublicationOrchestrator::class);
        $orchestrator->expects(self::never())->method('transition');

        foreach ([
            [ListingModerationIntentReservationV1::AlreadyApplied, ListingModerationCommandResultV1::AlreadyApplied],
            [ListingModerationIntentReservationV1::DivergentIntent, ListingModerationCommandResultV1::DivergentIntent],
        ] as [$reservation, $expected]) {
            $result = (new OwnerListingModerationCommandGatewayV1(
                new BoundaryIntentStoreStub($reservation),
                new BoundaryTransactionStub,
                $orchestrator,
                BoundaryWorkflowStoreStub::found($this->listingId(), ListingPublicationState::Published, 3),
            ))->apply(
                $this->listingId(),
                ListingModerationActionV1::Suspend,
                '53c30000-0000-4000-8000-000000000102',
                str_repeat('b', 64),
                $this->now(),
            );
            self::assertSame($expected, $result);
        }
    }

    #[Test]
    public function gateway_maps_stale_f01_version_to_closed_version_conflict(): void
    {
        $orchestrator = $this->createMock(ListingPublicationOrchestrator::class);
        $orchestrator->method('transition')->willReturn(
            ListingPublicationOrchestrationResult::concurrencyConflict(
                ListingPublicationOrchestrationDiagnosticCode::VersionConflict,
            ),
        );
        $intents = new BoundaryIntentStoreStub(ListingModerationIntentReservationV1::Reserved);

        $result = (new OwnerListingModerationCommandGatewayV1(
            $intents,
            new BoundaryTransactionStub,
            $orchestrator,
            BoundaryWorkflowStoreStub::found($this->listingId(), ListingPublicationState::Published, 5),
        ))->apply(
            $this->listingId(),
            ListingModerationActionV1::Suspend,
            '53c30000-0000-4000-8000-000000000103',
            str_repeat('c', 64),
            $this->now(),
        );

        self::assertSame(ListingModerationCommandResultV1::VersionConflict, $result);
        self::assertSame(ListingModerationCommandResultV1::VersionConflict, $intents->completed);
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('53c30000-0000-4000-8000-000000000100');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}

final class BoundaryWorkflowStoreStub implements ListingPublicationWorkflowStore
{
    private function __construct(private ListingPublicationPersistenceReadResult $result) {}

    public static function found(ListingId $listingId, ListingPublicationState $state, int $version): self
    {
        return new self(ListingPublicationPersistenceReadResult::found(
            new ListingPublicationStoredState($listingId, $state, $version),
        ));
    }

    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult
    {
        return ListingPublicationPersistenceWriteResult::Applied;
    }

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult
    {
        return ListingPublicationPersistenceWriteResult::Applied;
    }

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult
    {
        return $this->result;
    }
}

final class BoundaryIntentStoreStub implements ListingModerationIntentStore
{
    public ?ListingModerationCommandResultV1 $completed = null;

    public function __construct(private ListingModerationIntentReservationV1 $reservation) {}

    public function reserve(string $commandId, string $checksum, DateTimeImmutable $recordedAt): ListingModerationIntentReservationV1
    {
        return $this->reservation;
    }

    public function find(string $commandId): ?ListingModerationIntentStateV1
    {
        return null;
    }

    public function complete(
        string $commandId,
        string $checksum,
        ListingModerationCommandResultV1 $result,
        DateTimeImmutable $recordedAt,
    ): bool {
        $this->completed = $result;

        return true;
    }
}

final class BoundaryTransactionStub implements ListingModerationIntentTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
