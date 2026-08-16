<?php

namespace App\Application\PropertyListingAuthoringHttp;

use App\Application\PropertyAuthoringSourceCompleteness\Contract\PropertyAuthoringStateEnricherV1;
use App\Application\PropertyAuthoringSourceCompleteness\PropertyAuthoringEnrichmentStatus;
use App\Application\PropertyListingAuthoringHttp\Contract\PropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingDraftState;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\ListingOwnershipState;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;

final readonly class DeterministicPropertyListingAuthoringHttpRuntime implements PropertyListingAuthoringHttpRuntime
{
    public function __construct(
        private PropertyListingAuthoringRuntimeV1 $runtime,
        private PropertyAuthoringStateEnricherV1 $propertyEnricher,
    ) {}

    public function execute(
        PropertyListingAuthoringHttpOperation $operation,
        string $accountId,
        ?string $resourceId,
        ?string $intentId,
        array $input,
    ): PropertyListingAuthoringHttpResult {
        if ($this->runtime->inspect()->status !== PropertyListingAuthoringRuntimeStatus::Ready) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Unavailable);
        }

        return match ($operation) {
            PropertyListingAuthoringHttpOperation::InitiateProperty,
            PropertyListingAuthoringHttpOperation::PatchProperty => $this->writeProperty($accountId, $resourceId, $intentId, $input),
            PropertyListingAuthoringHttpOperation::ReadProperty => $this->readProperty($accountId, $resourceId),
            PropertyListingAuthoringHttpOperation::ReadDraft => $this->readDraft($accountId, $resourceId),
            PropertyListingAuthoringHttpOperation::PatchDraft => $this->writeDraft($accountId, $resourceId, $intentId, $input),
            PropertyListingAuthoringHttpOperation::AssessCompleteness => $this->completeness($accountId, $resourceId),
            PropertyListingAuthoringHttpOperation::GrantDelegation,
            PropertyListingAuthoringHttpOperation::RevokeDelegation => $this->delegate($operation, $accountId, $resourceId, $intentId, $input),
            PropertyListingAuthoringHttpOperation::Portfolio => $this->portfolio($accountId),
            PropertyListingAuthoringHttpOperation::CreateListing,
            PropertyListingAuthoringHttpOperation::RequestSubmission => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Unavailable),
        };
    }

    /** @param array<string, mixed> $input */
    private function writeProperty(string $accountId, ?string $id, ?string $intentId, array $input): PropertyListingAuthoringHttpResult
    {
        if ($id === null || $intentId === null) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Invalid);
        }
        $store = $this->runtime->propertyAuthoring();
        $current = $store->read($id);
        $expected = (int) ($input['expectedVersion'] ?? 0);
        $enriched = $this->propertyEnricher->enrich(
            $id,
            $accountId,
            $expected + 1,
            $intentId,
            $current,
            $input,
        );
        if ($enriched->status !== PropertyAuthoringEnrichmentStatus::Validated || $enriched->state === null) {
            return new PropertyListingAuthoringHttpResult($enriched->status === PropertyAuthoringEnrichmentStatus::DependencyUnavailable
                ? PropertyListingAuthoringHttpStatus::Unavailable
                : PropertyListingAuthoringHttpStatus::Invalid);
        }
        $result = $store->save($enriched->state, $expected);

        return $this->propertyResult($result, $expected + 1);
    }

    private function readProperty(string $accountId, ?string $id): PropertyListingAuthoringHttpResult
    {
        $state = $id === null ? null : $this->runtime->propertyAuthoring()->read($id);
        if ($state === null || ! hash_equals($state->ownerAccountId, $accountId)) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden);
        }

        return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, [
            'propertyId' => $state->propertyId,
            'propertyType' => $state->propertyType,
            'city' => $state->city,
            'neighborhood' => $state->neighborhood,
            'propertyReference' => $state->propertyReference,
            'surfaceSquareMeters' => $state->surfaceSquareMeters,
            'rooms' => $state->rooms,
            'bathrooms' => $state->bathrooms,
            'constructionYear' => $state->constructionYear,
            'geographicPlaceId' => $state->geographicPlaceId,
            'addressLine' => $state->addressLine,
            'sourceCompleteness' => $state->completeness()->value,
            'version' => $state->version,
        ]);
    }

    private function readDraft(string $accountId, ?string $id): PropertyListingAuthoringHttpResult
    {
        $draft = $id === null ? null : $this->runtime->listingDraft()->read($id);
        $ownership = $id === null ? null : $this->runtime->listingOwnership()->read($id);
        if ($draft === null || ! $this->authorized($ownership, $accountId, 'VIEW')) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden);
        }

        return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, [
            'listingId' => $draft->listingId,
            'propertyId' => $draft->propertyId,
            'title' => $draft->title,
            'description' => $draft->description,
            'transactionKind' => $draft->transactionKind,
            'priceMinor' => $draft->priceMinor,
            'currency' => $draft->currency,
            'chargesMinor' => $draft->chargesMinor,
            'availabilityDate' => $draft->availabilityDate,
            'contactPreference' => $draft->contactPreference,
            'version' => $draft->version,
        ]);
    }

    /** @param array<string, mixed> $input */
    private function writeDraft(string $accountId, ?string $id, ?string $intentId, array $input): PropertyListingAuthoringHttpResult
    {
        $current = $id === null ? null : $this->runtime->listingDraft()->read($id);
        $ownership = $id === null ? null : $this->runtime->listingOwnership()->read($id);
        if ($current === null || ! $this->authorized($ownership, $accountId, 'EDIT') || $intentId === null) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $expected = (int) ($input['expectedVersion'] ?? -1);
        $candidate = new ListingDraftState(
            $current->listingId,
            $current->propertyId,
            (string) ($input['title'] ?? $current->title),
            (string) ($input['description'] ?? $current->description),
            (string) ($input['transactionKind'] ?? $current->transactionKind),
            isset($input['priceMinor']) ? (int) $input['priceMinor'] : $current->priceMinor,
            isset($input['currency']) ? (string) $input['currency'] : $current->currency,
            isset($input['chargesMinor']) ? (int) $input['chargesMinor'] : $current->chargesMinor,
            isset($input['availabilityDate']) ? (string) $input['availabilityDate'] : $current->availabilityDate,
            (string) ($input['contactPreference'] ?? $current->contactPreference),
            $expected + 1,
            $intentId,
            $this->checksum($input),
        );

        return $this->listingResult($this->runtime->listingDraft()->save($candidate, $expected), $expected + 1);
    }

    private function completeness(string $accountId, ?string $id): PropertyListingAuthoringHttpResult
    {
        $result = $this->readDraft($accountId, $id);
        if ($result->status !== PropertyListingAuthoringHttpStatus::Succeeded) {
            return $result;
        }
        $missing = [];
        foreach (['title', 'description', 'transactionKind', 'priceMinor', 'currency', 'contactPreference'] as $field) {
            if (! isset($result->data[$field]) || $result->data[$field] === '') {
                $missing[] = $field;
            }
        }

        return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, [
            'complete' => $missing === [],
            'missingCodes' => $missing,
        ]);
    }

    /** @param array<string, mixed> $input */
    private function delegate(PropertyListingAuthoringHttpOperation $operation, string $accountId, ?string $id, ?string $intentId, array $input): PropertyListingAuthoringHttpResult
    {
        $current = $id === null ? null : $this->runtime->listingOwnership()->read($id);
        if ($current === null || ! hash_equals($current->ownerAccountId, $accountId) || $intentId === null) {
            return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden);
        }
        $delegate = (string) ($input['delegateAccountId'] ?? '');
        $permissions = array_values(array_unique(array_map('strval', (array) ($input['permissions'] ?? []))));
        $delegations = $current->delegations;
        if ($operation === PropertyListingAuthoringHttpOperation::GrantDelegation) {
            $delegations[$delegate] = $permissions;
        } else {
            unset($delegations[$delegate]);
        }
        $expected = (int) ($input['expectedVersion'] ?? -1);
        $candidate = new ListingOwnershipState(
            $current->listingId,
            $current->propertyId,
            $current->ownerAccountId,
            $delegations,
            $expected + 1,
            $intentId,
            $this->checksum($input),
        );

        return $this->listingResult($this->runtime->listingOwnership()->save($candidate, $expected), $expected + 1);
    }

    private function portfolio(string $accountId): PropertyListingAuthoringHttpResult
    {
        $items = array_map(static fn ($item): array => [
            'listingId' => $item->listingId,
            'propertyId' => $item->propertyId,
            'relation' => $item->relation,
            'draftVersion' => $item->draftVersion,
            'ownershipVersion' => $item->ownershipVersion,
            'completenessCode' => $item->completenessCode,
            'sourceCheckpoint' => $item->sourceCheckpoint,
        ], $this->runtime->authoringPortfolio()->listFor($accountId));

        return new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, ['items' => $items]);
    }

    private function authorized(?ListingOwnershipState $state, string $accountId, string $permission): bool
    {
        return $state !== null
            && (hash_equals($state->ownerAccountId, $accountId)
                || in_array($permission, $state->delegations[$accountId] ?? [], true));
    }

    private function propertyResult(PropertyAuthoringPersistenceWriteResult $result, int $version): PropertyListingAuthoringHttpResult
    {
        return match ($result) {
            PropertyAuthoringPersistenceWriteResult::Applied,
            PropertyAuthoringPersistenceWriteResult::AlreadyApplied => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, ['version' => $version]),
            PropertyAuthoringPersistenceWriteResult::DivergentIntent,
            PropertyAuthoringPersistenceWriteResult::VersionConflict,
            PropertyAuthoringPersistenceWriteResult::IdentityConflict => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Conflict),
            PropertyAuthoringPersistenceWriteResult::Rejected => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden),
        };
    }

    private function listingResult(AuthoringPersistenceWriteResult $result, int $version): PropertyListingAuthoringHttpResult
    {
        return match ($result) {
            AuthoringPersistenceWriteResult::Applied,
            AuthoringPersistenceWriteResult::AlreadyApplied => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Succeeded, ['version' => $version]),
            AuthoringPersistenceWriteResult::DivergentIntent,
            AuthoringPersistenceWriteResult::VersionConflict,
            AuthoringPersistenceWriteResult::IdentityConflict => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::Conflict),
            AuthoringPersistenceWriteResult::Rejected => new PropertyListingAuthoringHttpResult(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden),
        };
    }

    /** @param array<string, mixed> $input */
    private function checksum(array $input): string
    {
        ksort($input);

        return hash('sha256', (string) json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
