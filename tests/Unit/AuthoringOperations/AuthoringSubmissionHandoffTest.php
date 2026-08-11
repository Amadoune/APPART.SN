<?php

namespace Tests\Unit\AuthoringOperations;

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
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AuthoringSubmissionHandoffTest extends TestCase
{
    public function test_complete_authorized_submission_uses_only_the_public_f01_submit_contract(): void
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
        $publication = $this->createMock(ListingPublicationOrchestrator::class);
        $publication->expects(self::once())->method('transition')
            ->with(self::callback(static fn (ListingPublicationOrchestrationRequest $request): bool => $request->action === ListingPublicationAction::Submit && $request->expectedVersion === 1))
            ->willReturn(ListingPublicationOrchestrationResult::concurrencyConflict(
                ListingPublicationOrchestrationDiagnosticCode::VersionConflict,
            ));
        $publicFacts = $this->createMock(AuthoringPublicFactHandoffV1::class);
        $publicFacts->expects(self::once())->method('prepare')
            ->with(self::callback(static fn (AuthoringPublicFactSnapshot $snapshot): bool => $snapshot->listingId === '65000000-0000-4000-8000-000000000003'
                && $snapshot->authoringVersion === 1
                && $snapshot->transactionKind === PublicTransactionKind::Sale))
            ->willReturn(PublicFactHandoffResult::Applied);
        $operations = new DeterministicPropertyListingAuthoringOperations(
            $runtime,
            $this->createMock(CreateListingDraftV1::class),
            new class implements ListingCreationTransaction
            {
                public function run(Closure $operation): mixed
                {
                    return $operation();
                }
            },
            $publication,
            $publicFacts,
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
        ));

        self::assertSame(AuthoringOperationStatus::ConcurrentModification, $result->status);
    }
}
