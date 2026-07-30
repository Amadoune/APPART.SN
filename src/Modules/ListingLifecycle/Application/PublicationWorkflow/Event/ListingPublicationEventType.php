<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

enum ListingPublicationEventType: string
{
    case ListingSubmitted = 'listing.publication.submitted';
    case ListingResubmitted = 'listing.publication.resubmitted';
    case ListingReviewStarted = 'listing.publication.review_started';
    case ListingMaterialChangeReviewStarted = 'listing.publication.material_change_review_started';
    case ListingRenewalReviewStarted = 'listing.publication.renewal_review_started';
    case ListingRepublicationReviewStarted = 'listing.publication.republication_review_started';
    case ListingPublished = 'listing.publication.published';
    case ListingChangesRequested = 'listing.publication.changes_requested';
    case ListingRejected = 'listing.publication.rejected';
    case ListingWithdrawn = 'listing.publication.withdrawn';
    case ListingSuspended = 'listing.publication.suspended';
    case ListingReinstated = 'listing.publication.reinstated';
    case ListingExpired = 'listing.publication.expired';
    case ListingRenewed = 'listing.publication.renewed';
    case ListingArchived = 'listing.publication.archived';
}
