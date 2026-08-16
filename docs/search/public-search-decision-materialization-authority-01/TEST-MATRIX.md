# Future Test Matrix

## Unit

- visibilité depuis les trois états sources ;
- rank selon l'autorité future ;
- facettes exactes, normalisation, ordre et rejets ;
- SourceRevisionSet stable ;
- identité et version déterministes ;
- replay, obsolete, divergent et failures.

## PostgreSQL

- vraie décision `Applied` puis `AlreadyApplied` ;
- version monotone ;
- concurrence ;
- checksum divergent ;
- catch-up d'un Published existant.

## Architecture

- owner SearchDiscovery ;
- aucun import/SQL Projection ;
- aucune écriture cross-domain ;
- aucune route/UX Search ;
- aucune transaction distribuée.

## Intégration

`Published → SearchDecision → Reader Found → ProjectionSourceAssembly sans SearchMissing`.

Cette matrice est préparatoire; aucun test n'est créé ou exécuté dans l'Authority documentaire.

## Completion 01 — assertions fermées

- ranking policy v1 retourne `0` et `[]` ;
- identité UUIDv5 stable par ListingId + policyId ;
- première décision version 1 ;
- replay inchangé conserve version et retourne AlreadyApplied ;
- révisions dominantes produisent `current+1` ;
- candidate dominée retourne RejectedObsolete ;
- ensemble incomparable ou même version divergente retourne Divergent ;
- Property revision provient du ledger de promotion positif ;
- consumer Published et catch-up utilisent le même matérialiseur ;
- Reader Found lève SearchMissing pour l'assemblage Projection.
