<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use Throwable;

final readonly class OwnerListingModerationReaderV1 implements ListingModerationReaderV1
{
    public function __construct(
        private ListingPublicationWorkflowStore $workflowStore,
        private ListingPublicationWorkflow $workflow,
    ) {}

    public function read(
        ListingId $listingId,
        DateTimeImmutable $observedAt,
    ): ListingModerationEligibilityV1 {
        unset($observedAt);

        try {
            $current = $this->workflowStore->read($listingId);
        } catch (Throwable) {
            return ListingModerationEligibilityV1::DependencyUnavailable;
        }

        if ($current->status === ListingPublicationPersistenceReadStatus::Missing) {
            return ListingModerationEligibilityV1::Missing;
        }
        if ($current->status !== ListingPublicationPersistenceReadStatus::Found || $current->snapshot === null) {
            return ListingModerationEligibilityV1::Corrupted;
        }

        foreach (ListingModerationActionV1::cases() as $action) {
            if ($this->workflow->decide($current->snapshot->state, self::publicationAction($action))->transition !== null) {
                return ListingModerationEligibilityV1::Eligible;
            }
        }

        return ListingModerationEligibilityV1::Ineligible;
    }

    private static function publicationAction(ListingModerationActionV1 $action): ListingPublicationAction
    {
        return ListingPublicationAction::from($action->value);
    }
}
