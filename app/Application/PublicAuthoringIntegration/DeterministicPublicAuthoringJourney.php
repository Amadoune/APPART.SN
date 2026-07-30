<?php

namespace App\Application\PublicAuthoringIntegration;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PublicAuthoringIntegration\Contract\PublicAuthoringJourney;

final readonly class DeterministicPublicAuthoringJourney implements PublicAuthoringJourney
{
    public function __construct(private PropertyListingAuthoringOperations $operations) {}

    public function execute(PublicAuthoringJourneyRequest $request): PublicAuthoringJourneyResponse
    {
        $result = $this->operations->execute(new AuthoringOperationCommand(
            $request->operation->authoringOperation(),
            $request->intentId,
            $request->accountId,
            $request->propertyId,
            $request->listingId,
            $request->expectedVersion,
            $request->data,
            $request->occurredAt,
        ));

        return new PublicAuthoringJourneyResponse(match ($result->status) {
            AuthoringOperationStatus::Applied => PublicAuthoringJourneyStatus::Succeeded,
            AuthoringOperationStatus::AlreadyApplied => PublicAuthoringJourneyStatus::AcceptedReplay,
            AuthoringOperationStatus::NotFoundOrForbidden => PublicAuthoringJourneyStatus::NotFound,
            AuthoringOperationStatus::Invalid => PublicAuthoringJourneyStatus::Invalid,
            AuthoringOperationStatus::Incomplete => PublicAuthoringJourneyStatus::Incomplete,
            AuthoringOperationStatus::DivergentIntent,
            AuthoringOperationStatus::ConcurrentModification,
            AuthoringOperationStatus::LifecycleConflict => PublicAuthoringJourneyStatus::Conflict,
            AuthoringOperationStatus::DependencyUnavailable => PublicAuthoringJourneyStatus::Unavailable,
        }, $result->data);
    }
}
