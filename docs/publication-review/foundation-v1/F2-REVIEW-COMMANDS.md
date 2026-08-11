# F2 — Review Commands Implementation (Reopening)

## Frontière matérialisée

`BeginPublicationReviewV1` et `ApprovePublicationV1` sont les deux seules commandes ajoutées. Leur entrée est limitée à `queueItemId`, `commandId`, `expectedVersion`, `actor` et `occurredAt`.

La composition est strictement mécanique :

| Commande PublicationReview | Contrôle owner-scoped | Délégation unique | Synchronisation |
|---|---|---|---|
| Begin | QueueItem `claimed`, version et claimant | `ListingPublicationCommandGatewayV1::beginReview()` | état `claimed`, version + 1 |
| Approve | QueueItem `claimed`, version et claimant | `ListingPublicationCommandGatewayV1::approveAndPublish()` | état terminal `completed`, version + 1 |

PublicationReview ne résout ni Revision, ni Evidence, ni Expiration, ni MediaCollection, ni Property, ni Public Facts. Ces autorités restent encapsulées par la Gateway Listing Lifecycle certifiée.

## Résultats fermés

Les réductions exposées sont : `Applied`, `AlreadyApplied`, `VersionConflict`, `StateConflict`, `Missing`, `DivergentCommand`, `GatewayRejected` et `DependencyUnavailable`. Aucun fallback n'est appliqué.

## Transactions et replay

PublicationReview conserve sa transaction locale, son advisory lock et son command ledger owner-scoped. La Gateway conserve sa propre transaction locale et utilise un savepoint lorsqu'elle est appelée dans la transaction PublicationReview. Une erreur de synchronisation de file annule ainsi l'opération locale complète sans transaction distribuée.

Les migrations 096 et 097 et leurs rollbacks ne sont pas modifiés.
