<?php

namespace App\Application\AuthoringDraftResume;

use App\Application\AuthoringDraftResume\Contract\AuthoringDraftResumeReaderV1;
use App\Application\MediaAuthoringHttp\Contract\MediaAuthoringHttpRuntime;
use App\Application\MediaAuthoringHttp\MediaAuthoringHttpStatus;
use App\Application\PropertyListingAuthoringRuntime\Contract\PropertyListingAuthoringRuntimeV1;
use App\Application\PropertyListingAuthoringRuntime\PropertyListingAuthoringRuntimeStatus;
use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Throwable;

final readonly class DeterministicAuthoringDraftResumeReaderV1 implements AuthoringDraftResumeReaderV1
{
    public function __construct(
        private PropertyListingAuthoringRuntimeV1 $authoring,
        private ListingRegistry $listings,
        private ListingPublicationWorkflowStore $workflows,
        private MediaAuthoringHttpRuntime $media,
        private PlaceRegistry $places,
    ) {}

    public function read(string $accountId, string $listingId): AuthoringDraftResumeResult
    {
        try {
            if ($this->authoring->inspect()->status !== PropertyListingAuthoringRuntimeStatus::Ready) {
                return $this->result(AuthoringDraftResumeStatus::DependencyUnavailable);
            }

            $portfolio = null;
            foreach ($this->authoring->authoringPortfolio()->listFor($accountId) as $item) {
                if (hash_equals($item->listingId, $listingId)) {
                    $portfolio = $item;
                    break;
                }
            }
            if ($portfolio === null) {
                return $this->result(AuthoringDraftResumeStatus::NotFoundOrForbidden);
            }

            $draft = $this->authoring->listingDraft()->read($listingId);
            $ownership = $this->authoring->listingOwnership()->read($listingId);
            if ($draft === null || $ownership === null || ! $this->authorized($ownership->ownerAccountId, $ownership->delegations, $accountId)) {
                return $this->result(AuthoringDraftResumeStatus::NotFoundOrForbidden);
            }

            if (! hash_equals($portfolio->propertyId, $draft->propertyId)
                || ! hash_equals($ownership->propertyId, $draft->propertyId)
                || $portfolio->draftVersion !== $draft->version
                || $portfolio->ownershipVersion !== $ownership->version) {
                return $this->result(AuthoringDraftResumeStatus::StateConflict);
            }

            $property = $this->authoring->propertyAuthoring()->read($draft->propertyId);
            if ($property === null) {
                return $this->result(AuthoringDraftResumeStatus::Incomplete);
            }
            if (! hash_equals($property->ownerAccountId, $ownership->ownerAccountId)) {
                return $this->result(AuthoringDraftResumeStatus::StateConflict);
            }

            $id = ListingId::fromString($listingId);
            $aggregate = $this->listings->find($id);
            $workflow = $this->workflows->read($id);
            if ($aggregate === null || $workflow->status === ListingPublicationPersistenceReadStatus::Missing || $workflow->snapshot === null) {
                return $this->result(AuthoringDraftResumeStatus::Incomplete);
            }
            if ($workflow->status === ListingPublicationPersistenceReadStatus::Corrupted) {
                return $this->result(AuthoringDraftResumeStatus::Corrupted);
            }
            if (! $aggregate->propertyId()->equals(PropertyId::fromString($draft->propertyId))
                || $aggregate->status() !== ListingStatus::Draft
                || $workflow->snapshot->state !== ListingPublicationState::Draft) {
                return $this->result(AuthoringDraftResumeStatus::StateConflict);
            }

            $geography = $this->geography($property->geographicPlaceId);
            if ($geography === null) {
                return $this->result(AuthoringDraftResumeStatus::Incomplete);
            }

            $media = $this->media->collection($accountId, $property->propertyId);
            if ($media->status === MediaAuthoringHttpStatus::Unavailable) {
                return $this->result(AuthoringDraftResumeStatus::DependencyUnavailable);
            }
            if ($media->status === MediaAuthoringHttpStatus::NotFoundOrForbidden) {
                return $this->result(AuthoringDraftResumeStatus::NotFoundOrForbidden);
            }
            if (! in_array($media->status, [MediaAuthoringHttpStatus::Available, MediaAuthoringHttpStatus::Empty], true)) {
                return $this->result(AuthoringDraftResumeStatus::Corrupted);
            }

            $mediaItems = array_values((array) ($media->data['items'] ?? []));
            $step = $this->step($property, $draft, $mediaItems);

            return $this->result(AuthoringDraftResumeStatus::Available, [
                'mode' => 'resume',
                'step' => $step,
                'propertyId' => $property->propertyId,
                'listingId' => $draft->listingId,
                'expectedAuthoringVersion' => $property->version,
                'expectedVersion' => $draft->version,
                'aggregateVersion' => $aggregate->version(),
                'workflowVersion' => $workflow->snapshot->version,
                'property' => [
                    'propertyType' => $property->propertyType,
                    'propertyReference' => $property->propertyReference,
                    'surfaceSquareMeters' => $property->surfaceSquareMeters,
                    'rooms' => $property->rooms,
                    'bathrooms' => $property->bathrooms,
                    'constructionYear' => $property->constructionYear,
                    'addressLine' => $property->addressLine,
                    'addressIntentId' => $property->addressIntentId,
                    'city' => $property->city,
                    'neighborhood' => $property->neighborhood,
                ],
                'draft' => [
                    'title' => $draft->title,
                    'description' => $draft->description,
                    'transactionKind' => $draft->transactionKind,
                    'priceMinor' => $draft->priceMinor,
                    'currency' => $draft->currency,
                    'chargesMinor' => $draft->chargesMinor,
                    'availabilityDate' => $draft->availabilityDate,
                    'contactPreference' => $draft->contactPreference,
                ],
                'geography' => $geography,
                'media' => [
                    'collectionId' => $media->data['collectionId'] ?? null,
                    'version' => $media->data['version'] ?? 0,
                    'items' => $mediaItems,
                ],
            ]);
        } catch (\UnexpectedValueException) {
            return $this->result(AuthoringDraftResumeStatus::Corrupted);
        } catch (Throwable) {
            return $this->result(AuthoringDraftResumeStatus::DependencyUnavailable);
        }
    }

    /** @param array<string, list<string>> $delegations */
    private function authorized(string $owner, array $delegations, string $account): bool
    {
        return hash_equals($owner, $account)
            || in_array('EDIT', $delegations[$account] ?? [], true);
    }

    /** @return array{geographicPlaceId: string, type: string, parentPlaceId: ?string, label: string, path: list<array{placeId: string, label: string, type: string, parentPlaceId: ?string}>}|null */
    private function geography(?string $placeId): ?array
    {
        if ($placeId === null) {
            return null;
        }
        $path = [];
        $seen = [];
        $place = $this->places->find(PlaceId::fromString($placeId));
        $selected = $place;
        while ($place instanceof Place) {
            if (isset($seen[$place->id()->value]) || count($path) >= 12 || ! $place->isEnabled() || $place->mergedInto() !== null) {
                throw new \UnexpectedValueException('Invalid Geography hierarchy.');
            }
            $seen[$place->id()->value] = true;
            array_unshift($path, [
                'placeId' => $place->id()->value,
                'label' => $place->officialName()->value,
                'type' => $place->type()->value,
                'parentPlaceId' => $place->parent()?->placeId->value,
            ]);
            $place = $place->parent() === null ? null : $this->places->find($place->parent()->placeId);
        }
        if (! $selected instanceof Place || ($selected->parent() !== null && count($path) < 2)) {
            return null;
        }

        return [
            'geographicPlaceId' => $selected->id()->value,
            'type' => $selected->type()->value,
            'parentPlaceId' => $selected->parent()?->placeId->value,
            'label' => $selected->officialName()->value,
            'path' => $path,
        ];
    }

    /** @param list<mixed> $media */
    private function step(object $property, object $draft, array $media): int
    {
        if ($draft->transactionKind === '') {
            return 1;
        }
        if ($property->propertyType === null || $property->propertyReference === null || $property->surfaceSquareMeters === null || $property->rooms === null || $property->bathrooms === null) {
            return 2;
        }
        if ($property->geographicPlaceId === null || $property->addressLine === null || $property->addressIntentId === null) {
            return 3;
        }
        if ($draft->title === '' || $draft->description === '' || $draft->priceMinor === null || $draft->currency === null || $draft->contactPreference === '') {
            return 4;
        }
        if ($media === []) {
            return 5;
        }

        return 6;
    }

    /** @param array<string, mixed> $snapshot */
    private function result(AuthoringDraftResumeStatus $status, array $snapshot = []): AuthoringDraftResumeResult
    {
        return new AuthoringDraftResumeResult($status, $snapshot);
    }
}
