# Idempotency Model

La convergence technique existante est : même ListingId + même version + même payload canonique → `AlreadyApplied`.

Pour garantir la sémantique demandée « mêmes sources + même décision », un futur matérialiseur devra aussi reproduire exactement :

- SearchIndexId ;
- version ;
- état ;
- rank ;
- facettes et ordre canonique ;
- SourceRevisionSet.

Ces préconditions ne sont pas fermées puisque rank, facettes productives, identité et version ne le sont pas. Aucun replay ne doit incrémenter la version ni déclencher Projection par lui-même.

## Completion 01

Les préconditions sont fermées : même ListingId, mêmes révisions, même policyId et même projection canonique conservent identité/version et retournent `AlreadyApplied`. La Projection reste une opération séparée explicitement rejouée.
