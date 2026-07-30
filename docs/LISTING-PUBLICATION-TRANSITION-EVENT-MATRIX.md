# Listing Publication Transition → Event Matrix

| État source | Action | État cible | Événement |
|---|---|---|---|
| Draft | Submit | Submitted | ListingSubmitted |
| Draft | Withdraw | Withdrawn | ListingWithdrawn |
| Draft | Archive | Archived | ListingArchived |
| Submitted | BeginReview | UnderReview | ListingReviewStarted |
| Submitted | Withdraw | Withdrawn | ListingWithdrawn |
| UnderReview | ApproveAndPublish | Published | ListingPublished |
| UnderReview | RequestChanges | ChangesRequested | ListingChangesRequested |
| UnderReview | Reject | Rejected | ListingRejected |
| UnderReview | Withdraw | Withdrawn | ListingWithdrawn |
| ChangesRequested | Submit | Submitted | ListingResubmitted |
| ChangesRequested | Withdraw | Withdrawn | ListingWithdrawn |
| ChangesRequested | Archive | Archived | ListingArchived |
| Published | ReviewMaterialChange | UnderReview | ListingMaterialChangeReviewStarted |
| Published | Suspend | Suspended | ListingSuspended |
| Published | Expire | Expired | ListingExpired |
| Published | Withdraw | Withdrawn | ListingWithdrawn |
| Suspended | Reinstate | Published | ListingReinstated |
| Suspended | RequestChanges | ChangesRequested | ListingChangesRequested |
| Suspended | Reject | Rejected | ListingRejected |
| Suspended | Archive | Archived | ListingArchived |
| Expired | ReviewRenewal | UnderReview | ListingRenewalReviewStarted |
| Expired | RenewDirectly | Published | ListingRenewed |
| Expired | Withdraw | Withdrawn | ListingWithdrawn |
| Expired | Archive | Archived | ListingArchived |
| Withdrawn | ApproveRepublication | UnderReview | ListingRepublicationReviewStarted |
| Withdrawn | Archive | Archived | ListingArchived |
| Rejected | Archive | Archived | ListingArchived |

Toutes les autres combinaisons sont absentes du catalogue et explicitement refusées.
