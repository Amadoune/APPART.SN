# Writer semantics

`PostgreSqlPublicGeographyWriter` implémente le contrat sans SQL Application. Il ouvre une transaction locale si nécessaire, prend un advisory transaction lock sur `placeId`, relit `FOR UPDATE`, puis applique la comparaison de version.

Résultats certifiés : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`. L'upsert est atomique; payload et checksum sont contraints.
