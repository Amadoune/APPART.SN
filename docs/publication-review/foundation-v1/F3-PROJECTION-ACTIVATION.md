# F3 — Projection Activation

## Frontière

`ProjectPublishedListingV1` reçoit exclusivement `listingId`, `publicationVersion`, `commandId` et `occurredAt`. PublicationReview exprime une intention d'activation et ne relit ni Search ni les sources de Projection.

La chaîne est :

`ProjectPublishedListingV1` → `PublicationReviewProjectionStore` → `PublicListingProjectionActivation` → `PublicListingProjectionUpdater`.

L'adaptateur réduit exhaustivement le catalogue du Projection Updater :

| Résultat Projection | Résultat F3 |
|---|---|
| `Applied` | `Applied` |
| `AlreadyApplied` | `AlreadyApplied` |
| source/projection indisponible ou promotion non prête | `NotReady` |
| watermark, canonical ou génération en conflit | `Conflict` |
| exception technique | `DependencyUnavailable` |

Aucun fallback et aucune écriture Search ne sont introduits.

## Synchronisation de file

L'activation exige un QueueItem `completed` dont `submissionVersion + 2` correspond à `publicationVersion`. Après succès, l'état terminal `completed` est conservé, sa version est incrémentée et le command ledger 096 enregistre l'activation. Cette solution conserve l'historique sans modifier le catalogue ni la migration 096.

Le replay d'un `commandId` identique retourne `AlreadyApplied` avant tout rappel du Projection Updater. Il ne réexécute donc jamais `ApproveAndPublish` ni Projection.
