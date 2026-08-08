<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\Contract\ListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Throwable;

final readonly class OwnerListingContactabilityReaderV1 implements ListingContactabilityReaderV1
{
    public function __construct(private ListingPublicationWorkflowStore $workflowStore) {}

    public function read(
        ListingId $listingId,
        ContactabilityObservedAt $observedAt,
    ): ListingContactabilityDecisionV1 {
        unset($observedAt);

        try {
            $current = $this->workflowStore->read($listingId);
        } catch (Throwable) {
            return ListingContactabilityDecisionV1::DependencyUnavailable;
        }

        if ($current->status === ListingPublicationPersistenceReadStatus::Missing) {
            return ListingContactabilityDecisionV1::Missing;
        }

        if ($current->status !== ListingPublicationPersistenceReadStatus::Found || $current->snapshot === null) {
            return ListingContactabilityDecisionV1::Corrupted;
        }

        return $current->snapshot->state === ListingPublicationState::Published
            ? ListingContactabilityDecisionV1::Contactable
            : ListingContactabilityDecisionV1::NotContactable;
    }
}
