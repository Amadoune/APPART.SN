# Current Reduction Evidence

## Before

`ProjectPublishedListingStatus::NotReady` → `PublicationReviewExperienceStatus::NotReady` → controller mode `confirmed` → “Publication confirmée”.

## After

`ProjectPublishedListingStatus::NotReady` → `PublicationReviewExperienceStatus::NotReady` → controller mode `not-ready` → explicit non-success state.

The existing statuses remain closed: Available, Applied, AlreadyApplied, Empty, Forbidden, NotFound, Conflict, NotReady and DependencyUnavailable. No status was added or reinterpreted.
