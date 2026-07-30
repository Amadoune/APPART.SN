<?php

namespace App\Application\MultiTargetDelivery;

use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\PropertyListingResolution\Contract\PropertyListingsResolver;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;

final readonly class PagedMultiTargetPropagationStrategy implements MultiTargetPropagationStrategy
{
    public function __construct(private PropertyListingsResolver $resolver) {}

    public function plan(MultiTargetPropagationRequest $request, ?string $checkpoint, int $limit): MultiTargetPropagationPlan
    {
        $page = $this->resolver->readPage($request->propertyId, $checkpoint, $limit);
        $status = match ($page->status) {
            PropertyListingsPageStatus::Found => MultiTargetPropagationStatus::TargetsAvailable,
            PropertyListingsPageStatus::Empty => MultiTargetPropagationStatus::NoTargets,
            PropertyListingsPageStatus::Completed => MultiTargetPropagationStatus::Completed,
            PropertyListingsPageStatus::InvalidIdentity => MultiTargetPropagationStatus::InvalidIdentity,
            PropertyListingsPageStatus::Corrupted => MultiTargetPropagationStatus::Corrupted,
        };

        return new MultiTargetPropagationPlan($request, $status, $page->listingIds, $page->nextCheckpoint, $page->completed, $page->diagnostic);
    }
}
