# Command Matrix

| Surface | Entrée minimale | Préconditions autoritatives | Sorties fermées | Effet |
|---|---|---|---|---|
| PublicationReviewQueueReaderV1 | state, cursor, limit, observedAt | autorisation review | Available, Empty, Corrupted, DependencyUnavailable | lecture uniquement |
| ClaimPublicationReviewV1 | queueItemId, commandId, actor, occurredAt | autorisation review, item disponible | Applied, AlreadyApplied, Conflict, Forbidden, DependencyUnavailable | claim borné |
| BeginPublicationReviewV1 | listingId, commandId, expectedVersion, actor, occurredAt | Submitted, autorisation review | Applied, AlreadyApplied, Denied, VersionConflict, Forbidden, DependencyUnavailable | délègue BeginReview |
| ApprovePublicationV1 | listingId, commandId, expectedVersion, actor, occurredAt | UnderReview, autorisation approve, exigences PublishListing satisfaites | Applied, AlreadyApplied, Denied, VersionConflict, Forbidden, DependencyUnavailable | délègue ApproveAndPublish |
| ProjectPublishedListingV1 | listingId, publicationVersion, commandId | état Published confirmé | Applied, AlreadyApplied, NotReady, Conflict, DependencyUnavailable | appelle Projection Updater |

## Données nécessaires à ApproveAndPublish

La commande ne peut inventer aucune donnée. Elle doit composer les autorités déjà qualifiées :

- `ListingRevisionId` via Listing Revision Authority ;
- `TransitionEvidence` depuis l’acteur IAM et l’instant de décision ;
- `MediaCollectionId` depuis la collection Media owner-compatible du Listing ;
- `ExpirationDate` depuis la Publication Expiration Policy ;
- version attendue depuis la lecture owner-scoped de la candidature.

## Garanties futures

Identité canonique de commande, idempotence stricte, optimistic locking, absence de fallback, transaction locale par mutation et reprise idempotente séparée de la projection.
