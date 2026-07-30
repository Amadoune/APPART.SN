# Matrice Rebuild Runtime

| Scope | Source des identités | Ordre | Pagination |
|---|---|---|---|
| Full | PK Listing PostgreSQL | UUID croissant | keyset |
| Listings | ensemble explicite | lexical stable | keyset sur liste |
| Range | PK Listing entre bornes inclusives | UUID croissant | keyset borné |

| État Candidate | Résultat inspecté | Record |
|---|---|---|
| source certifiée et Ready | `Built` | oui |
| source absente/corrompue | `SourceBlocked` | non |
| Geography/Media incomplet | `PromotionNotReady` | non |
| builder/policy certifié refuse | `CertifiedTransformationRejected` | non |
| ReadModel indisponible | `ProjectionUnavailable` | non |

Le Rebuilder conserve son reporting certifié : Applied, AlreadyApplied, Missing et rejected identities.
