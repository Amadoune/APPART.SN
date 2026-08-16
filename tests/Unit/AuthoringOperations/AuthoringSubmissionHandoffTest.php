<?php

namespace Tests\Unit\AuthoringOperations;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\DeterministicPropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeReport;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingDraftStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract\ListingOwnershipStore;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDiagnostic;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyResult;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyStatus;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class AuthoringSubmissionHandoffTest extends TestCase
{
    /** @return iterable<string, array{ListingPublicationOrchestrationResult|Throwable, AuthoringOperationStatus, bool}> */
    public static function workflowOutcomes(): iterable
    {
        $transition = new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit);

        yield 'applied commits' => [ListingPublicationOrchestrationResult::applied($transition), AuthoringOperationStatus::Applied, true];
        yield 'already applied commits' => [ListingPublicationOrchestrationResult::alreadyApplied($transition), AuthoringOperationStatus::AlreadyApplied, true];
        yield 'denied rolls back and preserves lifecycle conflict' => [ListingPublicationOrchestrationResult::denied(ListingPublicationDiagnostic::transitionForbidden()), AuthoringOperationStatus::LifecycleConflict, false];
        yield 'concurrency rolls back and preserves concurrent modification' => [ListingPublicationOrchestrationResult::concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode::VersionConflict), AuthoringOperationStatus::ConcurrentModification, false];
        yield 'persistence failure rolls back and preserves dependency unavailable' => [ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure), AuthoringOperationStatus::DependencyUnavailable, false];
        yield 'success without transition rolls back' => [self::outcomeWithoutTransition(), AuthoringOperationStatus::DependencyUnavailable, false];
        yield 'technical exception rolls back through technical reduction' => [new RuntimeException('workflow unavailable'), AuthoringOperationStatus::DependencyUnavailable, false];
    }

    #[DataProvider('workflowOutcomes')]
    public function test_workflow_outcome_explicitly_controls_commit_and_preserves_application_result(ListingPublicationOrchestrationResult|Throwable $outcome, AuthoringOperationStatus $expected, bool $committed): void
    {
        $runtime = $this->createMock(PropertyListingAuthoringRuntimeV1::class);
        $drafts = $this->createMock(ListingDraftStore::class);
        $ownerships = $this->createMock(ListingOwnershipStore::class);
        $runtime->method('inspect')->willReturn(new PropertyListingAuthoringRuntimeReport(PropertyListingAuthoringRuntimeStatus::Ready));
        $runtime->method('listingDraft')->willReturn($drafts);
        $runtime->method('listingOwnership')->willReturn($ownerships);
        $drafts->method('read')->willReturn(new ListingDraftState(
            '65000000-0000-4000-8000-000000000003',
            '65000000-0000-4000-8000-000000000002',
            'Titre',
            'Description',
            'sale',
            15000000,
            'XOF',
            null,
            null,
            'platform',
            1,
            '65000000-0000-4000-8000-000000000006',
            str_repeat('a', 64),
        ));
        $ownerships->method('read')->willReturn(new ListingOwnershipState(
            '65000000-0000-4000-8000-000000000003',
            '65000000-0000-4000-8000-000000000002',
            '65000000-0000-4000-8000-000000000001',
            [],
            1,
            '65000000-0000-4000-8000-000000000006',
            str_repeat('a', 64),
        ));
        $publication = $this->createMock(ListingPublicationEventOrchestrator::class);
        $transition = $publication->expects(self::once())->method('transition')
            ->with(self::callback(static fn (ListingPublicationEventOrchestrationRequest $request): bool => $request->transition->action === ListingPublicationAction::Submit
                && $request->transition->expectedVersion === 1
                && $request->metadata->occurredAt->value === '2026-07-27T18:02:00.000000Z'));
        $outcome instanceof Throwable
            ? $transition->willThrowException($outcome)
            : $transition->willReturn($outcome);
        $publicFacts = $this->createMock(AuthoringPublicFactHandoffV1::class);
        $publicFacts->expects(self::once())->method('prepare')
            ->with(self::callback(static fn (AuthoringPublicFactSnapshot $snapshot): bool => $snapshot->listingId === '65000000-0000-4000-8000-000000000003'
                && $snapshot->authoringVersion === 1
                && $snapshot->transactionKind === PublicTransactionKind::Sale))
            ->willReturn(PublicFactHandoffResult::Applied);
        $promotion = $this->createMock(PromoteAuthoredPropertyV1::class);
        $promotion->expects(self::once())->method('promote')
            ->willReturn(new PromoteAuthoredPropertyResult(PromoteAuthoredPropertyStatus::Applied));
        $transaction = new TrackingListingCreationTransaction;
        $operations = new DeterministicPropertyListingAuthoringOperations(
            $runtime,
            $this->createMock(CreateListingDraftV1::class),
            $transaction,
            $publication,
            $this->createStub(PropertyAuthoringStateEnricherV1::class),
            $publicFacts,
            null,
            null,
            $promotion,
        );

        $result = $operations->execute(new AuthoringOperationCommand(
            AuthoringOperation::SubmitListing,
            '65000000-0000-4000-8000-000000000009',
            '65000000-0000-4000-8000-000000000001',
            null,
            '65000000-0000-4000-8000-000000000003',
            1,
            [],
            new DateTimeImmutable('2026-07-27T18:02:00+00:00'),
            1,
        ));

        self::assertSame($expected, $result->status);
        self::assertSame($committed ? 1 : 0, $transaction->commits);
        self::assertSame($committed ? 0 : 1, $transaction->rollbacks);
    }

    private static function outcomeWithoutTransition(): ListingPublicationOrchestrationResult
    {
        $reflection = new \ReflectionClass(ListingPublicationOrchestrationResult::class);
        $result = $reflection->newInstanceWithoutConstructor();
        foreach ([
            'status' => ListingPublicationOrchestrationStatus::Applied,
            'transition' => null,
            'workflowDiagnostic' => null,
            'orchestrationDiagnostic' => null,
        ] as $property => $value) {
            $reflection->getProperty($property)->setValue($result, $value);
        }

        return $result;
    }
}

final class TrackingListingCreationTransaction implements ListingCreationTransaction
{
    public int $commits = 0;

    public int $rollbacks = 0;

    public function run(Closure $operation): mixed
    {
        try {
            $result = $operation();
            $this->commits++;

            return $result;
        } catch (Throwable $error) {
            $this->rollbacks++;
            throw $error;
        }
    }
}
