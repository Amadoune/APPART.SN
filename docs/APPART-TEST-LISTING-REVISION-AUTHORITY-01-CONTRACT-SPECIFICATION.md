# Listing Revision Authority 01 — Contract Specification

`ListingRevisionAllocatorV1::allocate(ListingId, ListingRevisionOperation, ListingRevisionIntentId): ListingRevisionId`

Opérations fermées : Submit, BeginReview, ApproveAndPublish. L'identité canonique couvre scope versionné, Listing, opération et intent. Aucun timestamp, état, acteur, reason, origin, média ou expiration n'entre dans le calcul.
