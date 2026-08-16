# Writer Semantics

`PostgreSqlContentSeoSourceSnapshotWriter` est l’écriture productive certifiée.

- transaction locale ;
- advisory lock par ListingId ;
- verrou de ligne `FOR UPDATE` ;
- SHA-256 du payload canonique ;
- `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent` ;
- rollback sur exception.

Une corruption est détectée à la lecture par reconstruction et comparaison du checksum. Aucune modification du writer n’est autorisée.
