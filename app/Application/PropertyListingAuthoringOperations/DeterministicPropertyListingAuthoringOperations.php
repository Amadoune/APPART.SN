<?php

namespace App\Application\PropertyListingAuthoringOperations;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use App\Application\PropertyAuthoringSourceCompleteness\PropertyAuthoringEnrichmentStatus;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftCommandV1;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftStatusV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionIntentId;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionOperation;
use Appart\Modules\ListingLifecycle\Application\UseCase\SubmitListing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyCommand;
use Appart\Modules\RealEstateCatalog\Application\Promotion\PromoteAuthoredPropertyStatus;
use Throwable;

final readonly class DeterministicPropertyListingAuthoringOperations implements PropertyListingAuthoringOperations
{
    public function __construct(
        private PropertyListingAuthoringRuntimeV1 $runtime,
        private CreateListingDraftV1 $createListing,
        private ListingCreationTransaction $listingTransaction,
        private ListingPublicationEventOrchestrator $publication,
        private PropertyAuthoringStateEnricherV1 $propertyEnricher,
        private ?AuthoringPublicFactHandoffV1 $publicFacts = null,
        private ?SubmitListing $submitListing = null,
        private ?ListingRevisionAllocatorV1 $revisions = null,
        private ?PromoteAuthoredPropertyV1 $promoteProperty = null,
    ) {}

    public function execute(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($this->runtime->inspect()->status !== PropertyListingAuthoringRuntimeStatus::Ready) {
            return new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable);
        }

        try {
            return match ($command->operation) {
                AuthoringOperation::InitiateProperty,
                AuthoringOperation::UpdateProperty => $this->property($command),
                AuthoringOperation::CreateListing => $this->create($command),
                AuthoringOperation::UpdateDraft => $this->draft($command),
                AuthoringOperation::GrantDelegation,
                AuthoringOperation::RevokeDelegation => $this->delegation($command),
                AuthoringOperation::SubmitListing => $this->submit($command),
            };
        } catch (Throwable) {
            return new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable);
        }
    }

    private function property(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($command->propertyId === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $store = $this->runtime->propertyAuthoring();
        $current = $store->read($command->propertyId);
        $enriched = $this->propertyEnricher->enrich(
            $command->propertyId,
            $command->actorAccountId,
            $command->expectedVersion + 1,
            $command->intentId,
            $current,
            $command->data,
        );
        if ($enriched->status !== PropertyAuthoringEnrichmentStatus::Validated || $enriched->state === null) {
            return new AuthoringOperationResult($enriched->status === PropertyAuthoringEnrichmentStatus::DependencyUnavailable
                ? AuthoringOperationStatus::DependencyUnavailable
                : AuthoringOperationStatus::Invalid);
        }
        $result = $store->save($enriched->state, $command->expectedVersion);

        return match ($result) {
            PropertyAuthoringPersistenceWriteResult::Applied => $this->success(AuthoringOperationStatus::Applied, $command->expectedVersion + 1),
            PropertyAuthoringPersistenceWriteResult::AlreadyApplied => $this->success(AuthoringOperationStatus::AlreadyApplied, $command->expectedVersion + 1),
            PropertyAuthoringPersistenceWriteResult::DivergentIntent => new AuthoringOperationResult(AuthoringOperationStatus::DivergentIntent),
            PropertyAuthoringPersistenceWriteResult::VersionConflict,
            PropertyAuthoringPersistenceWriteResult::IdentityConflict => new AuthoringOperationResult(AuthoringOperationStatus::ConcurrentModification),
            PropertyAuthoringPersistenceWriteResult::Rejected => new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden),
        };
    }

    private function create(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($command->propertyId === null || $command->listingId === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $property = $this->runtime->propertyAuthoring()->read($command->propertyId);
        if ($property === null || ! hash_equals($property->ownerAccountId, $command->actorAccountId)) {
            return new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden);
        }

        /** @var AuthoringOperationResult $result */
        $result = $this->listingTransaction->run(function () use ($command): AuthoringOperationResult {
            $created = $this->createListing->create(new CreateListingDraftCommandV1(
                $command->intentId,
                (string) $command->listingId,
                (string) $command->propertyId,
                (string) ($command->data['revisionId'] ?? ''),
                $command->actorAccountId,
                $command->occurredAt,
            ));
            if (! in_array($created->status, [CreateListingDraftStatusV1::Applied, CreateListingDraftStatusV1::AlreadyApplied], true)) {
                return $this->creationFailure($created->status);
            }

            $draft = new ListingDraftState(
                (string) $command->listingId,
                (string) $command->propertyId,
                (string) ($command->data['title'] ?? ''),
                (string) ($command->data['description'] ?? ''),
                (string) ($command->data['transactionKind'] ?? ''),
                isset($command->data['priceMinor']) ? (int) $command->data['priceMinor'] : null,
                isset($command->data['currency']) ? (string) $command->data['currency'] : null,
                isset($command->data['chargesMinor']) ? (int) $command->data['chargesMinor'] : null,
                isset($command->data['availabilityDate']) ? (string) $command->data['availabilityDate'] : null,
                (string) ($command->data['contactPreference'] ?? 'platform'),
                1,
                $command->intentId,
                $command->checksum(),
            );
            $ownership = new ListingOwnershipState(
                (string) $command->listingId,
                (string) $command->propertyId,
                $command->actorAccountId,
                [],
                1,
                $command->intentId,
                $command->checksum(),
            );
            $draftWrite = $this->runtime->listingDraft()->save($draft, 0);
            $ownershipWrite = $this->runtime->listingOwnership()->save($ownership, 0);
            if (! $this->converged($draftWrite) || ! $this->converged($ownershipWrite)) {
                throw new AuthoringOperationRollback;
            }
            $portfolioWrite = $this->runtime->authoringPortfolio()->project(new AuthoringPortfolioItem(
                $command->actorAccountId,
                (string) $command->listingId,
                (string) $command->propertyId,
                'OWNER',
                1,
                1,
                $this->completenessCode($draft),
                1,
            ));
            if (! $this->converged($portfolioWrite)) {
                throw new AuthoringOperationRollback;
            }

            return $this->success(
                $created->status === CreateListingDraftStatusV1::Applied
                    ? AuthoringOperationStatus::Applied
                    : AuthoringOperationStatus::AlreadyApplied,
                1,
            );
        });

        return $result;
    }

    private function draft(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($command->listingId === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $current = $this->runtime->listingDraft()->read($command->listingId);
        $ownership = $this->runtime->listingOwnership()->read($command->listingId);
        if ($current === null || ! $this->authorized($ownership, $command->actorAccountId, 'EDIT')) {
            return new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden);
        }
        $candidate = new ListingDraftState(
            $current->listingId,
            $current->propertyId,
            (string) ($command->data['title'] ?? $current->title),
            (string) ($command->data['description'] ?? $current->description),
            (string) ($command->data['transactionKind'] ?? $current->transactionKind),
            isset($command->data['priceMinor']) ? (int) $command->data['priceMinor'] : $current->priceMinor,
            isset($command->data['currency']) ? (string) $command->data['currency'] : $current->currency,
            isset($command->data['chargesMinor']) ? (int) $command->data['chargesMinor'] : $current->chargesMinor,
            isset($command->data['availabilityDate']) ? (string) $command->data['availabilityDate'] : $current->availabilityDate,
            (string) ($command->data['contactPreference'] ?? $current->contactPreference),
            $command->expectedVersion + 1,
            $command->intentId,
            $command->checksum(),
        );

        return $this->writeResult($this->runtime->listingDraft()->save($candidate, $command->expectedVersion), $candidate->version);
    }

    private function delegation(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($command->listingId === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $current = $this->runtime->listingOwnership()->read($command->listingId);
        if ($current === null || ! hash_equals($current->ownerAccountId, $command->actorAccountId)) {
            return new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden);
        }
        $delegate = (string) ($command->data['delegateAccountId'] ?? '');
        $permissions = array_values(array_unique(array_map('strval', (array) ($command->data['permissions'] ?? []))));
        if ($delegate === '' || array_diff($permissions, ['VIEW', 'EDIT', 'SUBMIT']) !== []) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $delegations = $current->delegations;
        if ($command->operation === AuthoringOperation::GrantDelegation) {
            $delegations[$delegate] = $permissions;
        } else {
            unset($delegations[$delegate]);
        }
        $candidate = new ListingOwnershipState(
            $current->listingId,
            $current->propertyId,
            $current->ownerAccountId,
            $delegations,
            $command->expectedVersion + 1,
            $command->intentId,
            $command->checksum(),
        );

        return $this->writeResult($this->runtime->listingOwnership()->save($candidate, $command->expectedVersion), $candidate->version);
    }

    private function submit(AuthoringOperationCommand $command): AuthoringOperationResult
    {
        if ($command->listingId === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::Invalid);
        }
        $draft = $this->runtime->listingDraft()->read($command->listingId);
        $ownership = $this->runtime->listingOwnership()->read($command->listingId);
        if ($draft === null || ! $this->authorized($ownership, $command->actorAccountId, 'SUBMIT')) {
            return new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden);
        }
        if ($this->completenessCode($draft) !== 'COMPLETE') {
            return new AuthoringOperationResult(AuthoringOperationStatus::Incomplete);
        }
        if ($this->promoteProperty === null || $command->expectedAuthoringVersion === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable);
        }
        $promotion = $this->promoteProperty->promote(new PromoteAuthoredPropertyCommand(
            $draft->propertyId,
            $command->actorAccountId,
            $command->expectedAuthoringVersion,
            $command->intentId,
            $command->occurredAt,
        ));
        if (! in_array($promotion->status, [PromoteAuthoredPropertyStatus::Applied, PromoteAuthoredPropertyStatus::AlreadyApplied], true)) {
            return new AuthoringOperationResult(match ($promotion->status) {
                PromoteAuthoredPropertyStatus::AuthoringMissing,
                PromoteAuthoredPropertyStatus::OwnershipMismatch => AuthoringOperationStatus::NotFoundOrForbidden,
                PromoteAuthoredPropertyStatus::IncompleteAuthoring => AuthoringOperationStatus::Incomplete,
                PromoteAuthoredPropertyStatus::VersionConflict => AuthoringOperationStatus::ConcurrentModification,
                PromoteAuthoredPropertyStatus::DivergentCommand => AuthoringOperationStatus::DivergentIntent,
                PromoteAuthoredPropertyStatus::DomainRejected => AuthoringOperationStatus::LifecycleConflict,
                PromoteAuthoredPropertyStatus::DependencyUnavailable => AuthoringOperationStatus::DependencyUnavailable,
            });
        }
        $handoff = null;
        try {
            $this->listingTransaction->run(function () use ($command, $draft, &$handoff): void {
                if ($this->publicFacts !== null) {
                    $prepared = $this->publicFacts->prepare(new AuthoringPublicFactSnapshot(
                        $draft->listingId,
                        $draft->version,
                        PublicTransactionKind::from($draft->transactionKind),
                        $command->intentId,
                        $command->checksum(),
                        $command->occurredAt,
                    ));
                    if (! in_array($prepared, [PublicFactHandoffResult::Applied, PublicFactHandoffResult::AlreadyApplied], true)) {
                        throw new AuthoringOperationRollback;
                    }
                }

                $listingId = ListingId::fromString($command->listingId);
                if ($this->submitListing !== null && $this->revisions !== null) {
                    $revision = $this->revisions->allocate($listingId, ListingRevisionOperation::Submit, ListingRevisionIntentId::fromString($command->intentId));
                    $this->submitListing->execute($listingId, $revision, new TransitionEvidence(
                        ActorId::fromString($command->actorAccountId),
                        TransitionTrigger::SubmissionConfirmed,
                        null,
                        TransitionOrigin::Advertiser,
                        $command->occurredAt,
                    ));
                }

                $instant = ListingPublicationEventInstant::fromCanonicalUtc(
                    $command->occurredAt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z'),
                );

                $handoff = $this->publication->transition(new ListingPublicationEventOrchestrationRequest(
                    new ListingPublicationOrchestrationRequest($listingId, ListingPublicationAction::Submit, $command->expectedVersion),
                    new ListingPublicationEventMetadata($instant, $instant),
                ));
                if (! $this->committable($handoff)) {
                    throw new AuthoringOperationRollback;
                }
            });
        } catch (AuthoringOperationRollback $rollback) {
            if (! $handoff instanceof ListingPublicationOrchestrationResult) {
                throw $rollback;
            }
        }
        if (! $handoff instanceof ListingPublicationOrchestrationResult) {
            throw new AuthoringOperationRollback;
        }
        if (in_array($handoff->status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true) && $handoff->transition === null) {
            return new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable);
        }

        return match ($handoff->status) {
            ListingPublicationOrchestrationStatus::Applied => $this->success(AuthoringOperationStatus::Applied, $command->expectedVersion + 1),
            ListingPublicationOrchestrationStatus::AlreadyApplied => $this->success(AuthoringOperationStatus::AlreadyApplied, $command->expectedVersion + 1),
            ListingPublicationOrchestrationStatus::Denied => new AuthoringOperationResult(AuthoringOperationStatus::LifecycleConflict),
            ListingPublicationOrchestrationStatus::ConcurrencyConflict => new AuthoringOperationResult(AuthoringOperationStatus::ConcurrentModification),
            ListingPublicationOrchestrationStatus::PersistenceFailure => new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable),
        };
    }

    private function committable(ListingPublicationOrchestrationResult $result): bool
    {
        return in_array($result->status, [ListingPublicationOrchestrationStatus::Applied, ListingPublicationOrchestrationStatus::AlreadyApplied], true)
            && $result->transition !== null;
    }

    private function creationFailure(CreateListingDraftStatusV1 $status): AuthoringOperationResult
    {
        return new AuthoringOperationResult(match ($status) {
            CreateListingDraftStatusV1::DivergentIntent => AuthoringOperationStatus::DivergentIntent,
            CreateListingDraftStatusV1::ListingIdConflict => AuthoringOperationStatus::ConcurrentModification,
            CreateListingDraftStatusV1::PropertyUnavailable => AuthoringOperationStatus::LifecycleConflict,
            CreateListingDraftStatusV1::InvalidCommand => AuthoringOperationStatus::Invalid,
            CreateListingDraftStatusV1::DependencyUnavailable,
            CreateListingDraftStatusV1::PersistenceFailure => AuthoringOperationStatus::DependencyUnavailable,
            CreateListingDraftStatusV1::Applied,
            CreateListingDraftStatusV1::AlreadyApplied => AuthoringOperationStatus::Applied,
        });
    }

    private function writeResult(AuthoringPersistenceWriteResult $result, int $version): AuthoringOperationResult
    {
        return match ($result) {
            AuthoringPersistenceWriteResult::Applied => $this->success(AuthoringOperationStatus::Applied, $version),
            AuthoringPersistenceWriteResult::AlreadyApplied => $this->success(AuthoringOperationStatus::AlreadyApplied, $version),
            AuthoringPersistenceWriteResult::DivergentIntent => new AuthoringOperationResult(AuthoringOperationStatus::DivergentIntent),
            AuthoringPersistenceWriteResult::VersionConflict,
            AuthoringPersistenceWriteResult::IdentityConflict => new AuthoringOperationResult(AuthoringOperationStatus::ConcurrentModification),
            AuthoringPersistenceWriteResult::Rejected => new AuthoringOperationResult(AuthoringOperationStatus::NotFoundOrForbidden),
        };
    }

    private function converged(AuthoringPersistenceWriteResult $result): bool
    {
        return in_array($result, [AuthoringPersistenceWriteResult::Applied, AuthoringPersistenceWriteResult::AlreadyApplied], true);
    }

    private function authorized(?ListingOwnershipState $state, string $accountId, string $permission): bool
    {
        return $state !== null
            && (hash_equals($state->ownerAccountId, $accountId)
                || in_array($permission, $state->delegations[$accountId] ?? [], true));
    }

    private function completenessCode(ListingDraftState $draft): string
    {
        return $draft->title !== ''
            && $draft->description !== ''
            && $draft->transactionKind !== ''
            && $draft->priceMinor !== null
            && $draft->currency !== null
            && $draft->contactPreference !== ''
                ? 'COMPLETE'
                : 'INCOMPLETE';
    }

    private function success(AuthoringOperationStatus $status, int $version): AuthoringOperationResult
    {
        return new AuthoringOperationResult($status, ['version' => $version]);
    }
}

final class AuthoringOperationRollback extends \RuntimeException {}
