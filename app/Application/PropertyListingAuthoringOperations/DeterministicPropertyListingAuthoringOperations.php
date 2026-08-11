<?php

namespace App\Application\PropertyListingAuthoringOperations;

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
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicTransactionKind;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Throwable;

final readonly class DeterministicPropertyListingAuthoringOperations implements PropertyListingAuthoringOperations
{
    public function __construct(
        private PropertyListingAuthoringRuntimeV1 $runtime,
        private CreateListingDraftV1 $createListing,
        private ListingCreationTransaction $listingTransaction,
        private ListingPublicationOrchestrator $publication,
        private ?AuthoringPublicFactHandoffV1 $publicFacts = null,
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
        $result = $store->save(new PropertyAuthoringState(
            $command->propertyId,
            $command->actorAccountId,
            $command->expectedVersion + 1,
            $command->intentId,
            $command->checksum(),
            isset($command->data['propertyType']) ? (string) $command->data['propertyType'] : $current?->propertyType,
            isset($command->data['city']) ? (string) $command->data['city'] : $current?->city,
            isset($command->data['neighborhood']) ? (string) $command->data['neighborhood'] : $current?->neighborhood,
        ), $command->expectedVersion);

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
        $handoff = $this->listingTransaction->run(function () use ($command, $draft): ListingPublicationOrchestrationResult {
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

            return $this->publication->transition(new ListingPublicationOrchestrationRequest(
                ListingId::fromString($command->listingId),
                ListingPublicationAction::Submit,
                $command->expectedVersion,
            ));
        });
        if (! $handoff instanceof ListingPublicationOrchestrationResult) {
            throw new AuthoringOperationRollback;
        }

        return match ($handoff->status) {
            ListingPublicationOrchestrationStatus::Applied => $this->success(AuthoringOperationStatus::Applied, $command->expectedVersion + 1),
            ListingPublicationOrchestrationStatus::AlreadyApplied => $this->success(AuthoringOperationStatus::AlreadyApplied, $command->expectedVersion + 1),
            ListingPublicationOrchestrationStatus::Denied => new AuthoringOperationResult(AuthoringOperationStatus::LifecycleConflict),
            ListingPublicationOrchestrationStatus::ConcurrencyConflict => new AuthoringOperationResult(AuthoringOperationStatus::ConcurrentModification),
            ListingPublicationOrchestrationStatus::PersistenceFailure => new AuthoringOperationResult(AuthoringOperationStatus::DependencyUnavailable),
        };
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
