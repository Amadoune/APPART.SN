# F1 — Queue & Claim Implementation

## Surface livrée

- `PublicationReviewQueueReaderV1` : lecture paginée et déterministe des items pending ;
- `PublicationReviewQueue` : ingestion owner-scoped des événements Submitted/Resubmitted ;
- `ClaimPublicationReviewV1` : claim versionné et idempotent ;
- `PublicationReviewCommandLedger` : preuve durable des commandes ;
- `PublicationReviewConsumer` : consumer logique unique ;
- `PostgreSqlPublicationReviewQueue` : repository PostgreSQL unique ;
- migration/rollback additifs 096 ;
- provider singleton et aliases nominatifs.

## Handoff

`AtomicListingPublicationEventOrchestrator` transmet chaque événement certifié au consumer PublicationReview après l'écriture de la delivery Projection, dans la même orchestration atomique. Le consumer ignore mécaniquement les événements autres que `ListingSubmitted` et `ListingResubmitted`.

La queue ne lit ni le store Lifecycle ni l'Outbox Projection. Elle reçoit l'événement immutable et persiste sa propre identité, son checksum et sa version.

## Pagination

Ordre stable : `submittedAt`, `listingId`, `submissionVersion`, `queueItemId`. Le curseur opaque encode uniquement cette clé. La limite est bornée de 1 à 100.

## Claim

Le claim porte `commandId`, `queueItemId`, `actor`, `occurredAt` et `expectedVersion`. Il combine :

- advisory lock transactionnel par item ;
- `SELECT ... FOR UPDATE` ;
- comparaison de version ;
- ledger par `commandId` et checksum canonique ;
- incrément atomique de version ;
- savepoint lorsque l'appelant possède déjà la transaction.

Résultats fermés : `Applied`, `AlreadyApplied`, `VersionConflict`, `Conflict`, `Missing`, `DivergentCommand`, `DependencyUnavailable`.

## Exclusions respectées

Aucune commande BeginReview ou ApprovePublication, aucune UI P08, aucune modification de règle Lifecycle, Projection, Search ou IAM.
