<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

final readonly class ListingPublicationEventCatalog
{
    /** @var array<string, ListingPublicationEventType> */
    private const array TRANSITION_EVENTS = [
        'draft>submit>submitted' => ListingPublicationEventType::ListingSubmitted,
        'draft>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'draft>archive>archived' => ListingPublicationEventType::ListingArchived,
        'submitted>begin_review>under_review' => ListingPublicationEventType::ListingReviewStarted,
        'submitted>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'under_review>approve_and_publish>published' => ListingPublicationEventType::ListingPublished,
        'under_review>request_changes>changes_requested' => ListingPublicationEventType::ListingChangesRequested,
        'under_review>reject>rejected' => ListingPublicationEventType::ListingRejected,
        'under_review>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'changes_requested>submit>submitted' => ListingPublicationEventType::ListingResubmitted,
        'changes_requested>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'changes_requested>archive>archived' => ListingPublicationEventType::ListingArchived,
        'published>review_material_change>under_review' => ListingPublicationEventType::ListingMaterialChangeReviewStarted,
        'published>suspend>suspended' => ListingPublicationEventType::ListingSuspended,
        'published>expire>expired' => ListingPublicationEventType::ListingExpired,
        'published>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'suspended>reinstate>published' => ListingPublicationEventType::ListingReinstated,
        'suspended>request_changes>changes_requested' => ListingPublicationEventType::ListingChangesRequested,
        'suspended>reject>rejected' => ListingPublicationEventType::ListingRejected,
        'suspended>archive>archived' => ListingPublicationEventType::ListingArchived,
        'expired>review_renewal>under_review' => ListingPublicationEventType::ListingRenewalReviewStarted,
        'expired>renew_directly>published' => ListingPublicationEventType::ListingRenewed,
        'expired>withdraw>withdrawn' => ListingPublicationEventType::ListingWithdrawn,
        'expired>archive>archived' => ListingPublicationEventType::ListingArchived,
        'withdrawn>approve_republication>under_review' => ListingPublicationEventType::ListingRepublicationReviewStarted,
        'withdrawn>archive>archived' => ListingPublicationEventType::ListingArchived,
        'rejected>archive>archived' => ListingPublicationEventType::ListingArchived,
    ];

    /** @return list<ListingPublicationEvent> */
    public function eventsFor(ListingId $listingId, ListingPublicationTransition $transition, int $publicationVersion, ListingPublicationEventMetadata $metadata): array
    {
        $key = implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]);
        $type = self::TRANSITION_EVENTS[$key] ?? throw new UnsupportedListingPublicationEventTransition('The transition has no certified publication event mapping.');
        $payload = new ListingPublicationEventPayload($listingId, $transition->from, $transition->to, $transition->action, $publicationVersion);
        $payloadVersion = ListingPublicationEventPayloadVersion::V1;

        return [new ListingPublicationEvent(
            ListingPublicationEventId::derive($type, $payloadVersion, $payload),
            $type,
            $payloadVersion,
            $payload,
            $metadata,
        )];
    }

    public function transitionCount(): int
    {
        return count(self::TRANSITION_EVENTS);
    }
}
