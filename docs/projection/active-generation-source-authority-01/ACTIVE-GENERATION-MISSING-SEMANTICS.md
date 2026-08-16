# Active Generation Missing Semantics

`PostgreSqlActiveGenerationReader::read()` retourne `Missing` lorsque la requête sur `public_projection.generations WHERE state='active'` ne renvoie aucune ligne. `CertifiedPublicListingProjectionSource` réduit cela en `ProjectionSourceAssemblyStatus::ActiveGenerationMissing`, puis le lookup en `SourceUnavailable` et l'activation PublicationReview en `NotReady`.

Ce statut ne déclenche volontairement ni création, ni réparation, ni fallback.
