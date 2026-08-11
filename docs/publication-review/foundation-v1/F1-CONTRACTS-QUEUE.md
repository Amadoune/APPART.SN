# F1 — Contracts & Queue

## Objet

Le jalon devait matérialiser les cinq surfaces `PublicationReviewQueueReaderV1`, `ClaimPublicationReviewV1`, `BeginPublicationReviewV1`, `ApprovePublicationV1` et `ProjectPublishedListingV1`.

## Audit de matérialisabilité

| Besoin | Surface existante observée | Qualification |
|---|---|---|
| Découvrir les Listings `Submitted` | `ListingPublicationWorkflowStore::read(ListingId)` | `MISSING` — lecture unitaire uniquement, aucun inventaire |
| Alimenter une file PublicationReview | événements Listing Lifecycle écrits dans la Public Projection Outbox | `MISSING` — aucun consumer ni handoff PublicationReview |
| Paginer la file | aucune source owner-scoped énumérable | `BLOCKED` |
| Réclamer un item | claim de la Public Projection Outbox | `NOT_APPLICABLE` — propriété du consumer de projection |
| Rejouer un claim par `commandId` | aucune persistance PublicationReview | `BLOCKED` |
| Déléguer BeginReview | `SendToReview` et orchestration Lifecycle existants | `PARTIAL` — dépend d'un item de file qualifié |
| Déléguer ApproveAndPublish | `PublishListing` et orchestration Lifecycle existants | `PARTIAL` — dépend d'un item de file qualifié |
| Projeter après Published | `PublicListingProjectionUpdater` et Outbox existants | `PASS` comme capacité aval, non comme queue de revue |

## Frontière non substituable

La Public Projection Outbox est alimentée avec un `PublicProjectionOutboxConsumerId` dédié à `public-projection-updater`. Ses lectures et claims sont indexés par ce consumer. Réutiliser sa delivery comme item de revue ferait de PublicationReview un consommateur de l'état opérationnel de Projection et pourrait retarder, réclamer ou altérer la livraison de projection.

Le `ListingPublicationWorkflowStore` ne fournit que `initialize`, `append` et `read(ListingId)`. Étendre ce store pour lister les `Submitted`, ou interroger directement sa table, modifierait la frontière certifiée de Listing Lifecycle ou introduirait un accès SQL hors contrat.

## Décision

La file owner-scoped réelle ne peut pas être matérialisée dans le périmètre autorisé sans l'un des éléments suivants, à ouvrir séparément par décision d'autorité :

1. un handoff événementiel Listing Lifecycle → PublicationReview avec consumer indépendant ;
2. une persistance PublicationReview additive portant queue, claim, `commandId`, version et replay ;
3. une migration additive et son rollback, si cette persistance est retenue.

Aucune de ces extensions n'est implicitement autorisée par le présent jalon. Aucun contrat exécutable partiel n'est créé, car il ne pourrait satisfaire « Queue réelle », claim, optimistic locking et replay.

## Statut

`NO GO PROPOSÉ` — cause racine unique : absence d'une source/persistance de file PublicationReview owner-scoped, indépendante de l'Outbox de projection.
