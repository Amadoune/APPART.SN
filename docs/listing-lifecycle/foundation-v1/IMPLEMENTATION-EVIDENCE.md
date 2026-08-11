# Implementation Evidence

## Livrables techniques

- contrat `ListingPublicationCommandGatewayV1` ;
- Result et Status V1 fermés ;
- ledger/transaction owner-scoped Listing Lifecycle ;
- Gateway déterministe ;
- Provider singleton et binding nominatif ;
- migration additive 097 et rollback ;
- tests Unit, Architecture et PostgreSQL ciblés.

## Synchronisation démontrée

Le scénario Unit complet part d'un Aggregate et d'un workflow `Submitted` : BeginReview rend les deux `UnderReview`, puis ApproveAndPublish rend les deux `Published`. Le Registry relu contient l'Aggregate Published. Le replay identique retourne `AlreadyApplied` et un contenu divergent retourne `DivergentCommand`.

## Ledger 097

La persistance conserve commandId, ListingId, opération, checksum canonique, statut, versions workflow/Aggregate et timestamps. L'advisory lock transactionnel et `ON CONFLICT DO NOTHING` protègent les réservations concurrentes.

Tout SQL reste dans `PostgreSqlListingPublicationGatewayPersistence`. Les contrats et la Gateway Application n'en contiennent aucun.
